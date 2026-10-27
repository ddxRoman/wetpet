<?php

namespace App\Http\Controllers;

use App\Models\AutoRenewal;
use App\Models\BoostPayment;
use App\Models\BoostSubscription;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Specialist;
use App\Services\Boost\BoostService;
use App\Services\Payments\AutoRenewalService;
use App\Services\Payments\YooKassaService;
use App\Support\OwnerAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * «Продвижение» в кабинете владельца: пакеты, оплата (пока тестовый шлюз), статус подписок.
 */
class OwnerBoostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(string $type, int $id, BoostService $boost)
    {
        $this->authorizeOwner($type, $id);
        $entity = $this->entity($type, $id);

        $subscriptions = BoostSubscription::where('entity_type', $type)
            ->where('entity_id', $id)
            ->get()
            ->keyBy('package');

        $autoRenewals = AutoRenewal::where('kind', 'boost')
            ->where('entity_type', $type)
            ->where('entity_id', $id)
            ->where('status', 'active')
            ->get()
            ->keyBy('package');

        $payments = BoostPayment::where('entity_type', $type)
            ->where('entity_id', $id)
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(10)
            ->get();

        $gateway = $boost->gateway();

        return view('pages.owner.boost', [
            'type'          => $type,
            'entity'        => $entity,
            'plans'         => $boost->plans($type),
            'subscriptions' => $subscriptions,
            'autoRenewals'  => $autoRenewals,
            'payments'      => $payments,
            'gatewayReady'  => (bool) $gateway,
            'gatewayName'   => $gateway?->name(),
        ]);
    }

    public function checkout(Request $request, string $type, int $id, BoostService $boost)
    {
        $this->authorizeOwner($type, $id);
        $this->entity($type, $id);

        $data = $request->validate([
            'package' => ['required', Rule::in(array_keys($boost->plans($type)))],
            'period'  => ['required', Rule::in(array_keys(config('boost.periods')))],
        ]);

        $gateway = $boost->gateway();

        if (! $gateway) {
            return redirect()->route('owner.boost', ['type' => $type, 'id' => $id])
                ->with('boost_error', 'Оплата пока недоступна. Попробуйте позже.');
        }

        // Каждая покупка — подписка с автопродлением (пока доступно только через ЮKassa)
        $autoRenew = $gateway->name() === 'yookassa';

        $payment = $boost->createPayment(
            $request->user(), $type, $id, $data['package'], $data['period'], $gateway->name(), $autoRenew
        );

        try {
            $url = $gateway->createPayment($payment);
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'canceled']);

            return redirect()->route('owner.boost', ['type' => $type, 'id' => $id])
                ->with('boost_error', 'Не удалось создать платёж. Попробуйте позже.');
        }

        return redirect()->away($url);
    }

    /**
     * Сюда ЮKassa возвращает пользователя после оплаты (return_url).
     * Статус проверяем в API ЮKassa; то же делает вебхук, активация идемпотентна.
     */
    public function yookassaReturn(Request $request, BoostPayment $payment, YooKassaService $yookassa)
    {
        abort_unless($payment->gateway === 'yookassa' && $payment->user_id === $request->user()->id, 404);
        $this->authorizeOwner($payment->entity_type, (int) $payment->entity_id);

        try {
            $status = $yookassa->settleBoost($payment);
        } catch (\Throwable $e) {
            report($e);
            $status = 'pending';
        }

        $redirect = redirect()->route('owner.boost', ['type' => $payment->entity_type, 'id' => $payment->entity_id]);

        return match ($status) {
            'paid'     => $redirect->with('boost_success', 'Оплата прошла, пакет «' . config("boost.plans.{$payment->package}.title") . '» активирован.'),
            'canceled' => $redirect->with('boost_error', 'Платёж не был завершён. Деньги не списаны.'),
            default    => $redirect->with('boost_success', 'Платёж обрабатывается. Пакет активируется сразу после подтверждения оплаты.'),
        };
    }

    /**
     * Отключение подписки на пакет (автопродления). Пакет действует до конца оплаченного срока.
     */
    public function cancelAutoRenew(string $type, int $id, string $package, AutoRenewalService $renewals)
    {
        $this->authorizeOwner($type, $id);

        $renewal = AutoRenewal::where('scope_key', AutoRenewal::boostKey($type, $id, $package))
            ->where('status', 'active')
            ->first();

        if ($renewal) {
            $renewals->cancel($renewal);
        }

        return redirect()->route('owner.boost', ['type' => $type, 'id' => $id])
            ->with('boost_success', 'Подписка отключена и больше не будет продлеваться. Пакет действует до конца оплаченного срока.');
    }

    /* ---------- Тестовый шлюз (только когда PREMIUM_GATEWAY=fake) ---------- */

    public function fakeShow(Request $request, BoostPayment $payment)
    {
        $this->guardFake($request, $payment);

        return view('pages.owner.boost-fake-pay', [
            'payment' => $payment,
            'seoMeta' => ['title' => 'Тестовая оплата — Зверозор', 'robots' => 'noindex, nofollow'],
        ]);
    }

    public function fakeConfirm(Request $request, BoostPayment $payment, BoostService $boost)
    {
        $this->guardFake($request, $payment);

        $boost->activate($payment);

        return redirect()
            ->route('owner.boost', ['type' => $payment->entity_type, 'id' => $payment->entity_id])
            ->with('boost_success', 'Пакет «' . config("boost.plans.{$payment->package}.title") . '» активирован.');
    }

    private function guardFake(Request $request, BoostPayment $payment): void
    {
        abort_unless(config('premium.gateway') === 'fake', 404);
        abort_unless($payment->gateway === 'fake' && $payment->user_id === $request->user()->id, 404);
        abort_unless($payment->status === 'pending', 404);
        // Оплачивать можно только для карточки, которой владеешь
        $this->authorizeOwner($payment->entity_type, (int) $payment->entity_id);
    }

    private function authorizeOwner(string $type, int $id): void
    {
        abort_unless(in_array($type, ['clinic', 'organization', 'doctor', 'specialist'], true), 404);

        OwnerAccess::authorizeConfirmed(
            $type,
            $id,
            'Продвижение доступно подтверждённому владельцу карточки.'
        );
    }

    private function entity(string $type, int $id)
    {
        return match ($type) {
            'clinic'     => Clinic::findOrFail($id),
            'doctor'     => Doctor::findOrFail($id),
            'specialist' => Specialist::findOrFail($id),
            default      => Organization::findOrFail($id),
        };
    }
}
