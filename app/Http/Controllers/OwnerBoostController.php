<?php

namespace App\Http\Controllers;

use App\Models\BoostPayment;
use App\Models\BoostSubscription;
use App\Models\Clinic;
use App\Models\Organization;
use App\Services\Boost\BoostService;
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
            'plans'         => $boost->plans(),
            'subscriptions' => $subscriptions,
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
            'package' => ['required', Rule::in(array_keys($boost->plans()))],
            'period'  => ['required', Rule::in(array_keys(config('boost.periods')))],
        ]);

        $gateway = $boost->gateway();

        if (! $gateway) {
            return redirect()->route('owner.boost', ['type' => $type, 'id' => $id])
                ->with('boost_error', 'Оплата пока недоступна. Попробуйте позже.');
        }

        $payment = $boost->createPayment(
            $request->user(), $type, $id, $data['package'], $data['period'], $gateway->name()
        );

        return redirect()->away($gateway->createPayment($payment));
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
        abort_unless(in_array($type, ['clinic', 'organization'], true), 404);

        OwnerAccess::authorizeConfirmed(
            $type,
            $id,
            'Продвижение доступно подтверждённому владельцу карточки.'
        );
    }

    private function entity(string $type, int $id)
    {
        return $type === 'clinic' ? Clinic::findOrFail($id) : Organization::findOrFail($id);
    }
}
