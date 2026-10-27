<?php

namespace App\Http\Controllers;

use App\Models\AutoRenewal;
use App\Models\PremiumPayment;
use App\Services\Payments\AutoRenewalService;
use App\Services\Payments\YooKassaService;
use App\Services\Premium\PremiumService;
use Illuminate\Http\Request;

class PremiumController extends Controller
{
    /**
     * Оформление подписки: создаём платёж и отправляем пользователя в платёжную систему.
     */
    public function checkout(Request $request, PremiumService $premium)
    {
        $data = $request->validate([
            'plan' => 'required|in:month,year',
        ]);

        $gateway = $premium->gateway();

        if (! $gateway) {
            return redirect(route('account') . '#premium')
                ->with('premium_error', 'Оплата пока недоступна. Попробуйте позже.');
        }

        // Каждая покупка — подписка с автопродлением (пока доступно только через ЮKassa)
        $autoRenew = $gateway->name() === 'yookassa' && app(YooKassaService::class)->recurringEnabled();

        $payment = $premium->createPayment($request->user(), $data['plan'], $gateway->name(), $autoRenew);

        try {
            $url = $gateway->createPayment($payment);
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'canceled']);

            return redirect(route('account') . '#premium')
                ->with('premium_error', 'Не удалось создать платёж. Попробуйте позже.');
        }

        return redirect()->away($url);
    }

    /**
     * Сюда ЮKassa возвращает пользователя после оплаты (return_url).
     * Статус проверяем в API ЮKassa; то же делает вебхук, активация идемпотентна.
     */
    public function yookassaReturn(Request $request, PremiumPayment $payment, YooKassaService $yookassa)
    {
        abort_unless($payment->gateway === 'yookassa' && $payment->user_id === $request->user()->id, 404);

        try {
            $status = $yookassa->settlePremium($payment);
        } catch (\Throwable $e) {
            report($e);
            $status = 'pending';
        }

        $redirect = redirect(route('account') . '#premium');

        return match ($status) {
            'paid'     => $redirect->with('premium_success', 'Оплата прошла, Премиум-подписка активирована.'),
            'canceled' => $redirect->with('premium_error', 'Платёж не был завершён. Деньги не списаны.'),
            default    => $redirect->with('premium_success', 'Платёж обрабатывается. Подписка активируется сразу после подтверждения оплаты.'),
        };
    }

    /**
     * Отключение подписки (автопродления). Доступ сохраняется до конца оплаченного срока.
     */
    public function cancelAutoRenew(Request $request, AutoRenewalService $renewals)
    {
        $renewal = AutoRenewal::activePremium($request->user()->id);

        if ($renewal) {
            $renewals->cancel($renewal);
        }

        $until = $request->user()->premium_until;

        return redirect(route('account') . '#premium')->with(
            'premium_success',
            'Подписка отключена и больше не будет продлеваться.' . ($until && $until->isFuture() ? ' Премиум действует до ' . $until->format('d.m.Y') . '.' : '')
        );
    }

    /* ---------- Тестовый шлюз (только когда PREMIUM_GATEWAY=fake) ---------- */

    public function fakeShow(Request $request, PremiumPayment $payment)
    {
        $this->guardFake($request, $payment);

        return view('pages.premium.fake-pay', [
            'payment' => $payment,
            'seoMeta' => ['title' => 'Тестовая оплата — Зверозор', 'robots' => 'noindex, nofollow'],
        ]);
    }

    public function fakeConfirm(Request $request, PremiumPayment $payment, PremiumService $premium)
    {
        $this->guardFake($request, $payment);

        $premium->activate($payment);

        return redirect(route('account') . '#premium')
            ->with('premium_success', 'Премиум-подписка активирована.');
    }

    private function guardFake(Request $request, PremiumPayment $payment): void
    {
        abort_unless(config('premium.gateway') === 'fake', 404);
        abort_unless($payment->gateway === 'fake' && $payment->user_id === $request->user()->id, 404);
        abort_unless($payment->status === 'pending', 404);
    }
}
