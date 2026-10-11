<?php

namespace App\Http\Controllers;

use App\Models\PremiumPayment;
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

        $payment = $premium->createPayment($request->user(), $data['plan'], $gateway->name());

        return redirect()->away($gateway->createPayment($payment));
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
