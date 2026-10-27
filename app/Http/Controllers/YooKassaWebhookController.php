<?php

namespace App\Http\Controllers;

use App\Models\BoostPayment;
use App\Models\PremiumPayment;
use App\Services\Payments\YooKassaService;
use Illuminate\Http\Request;

/**
 * HTTP-уведомления ЮKassa (payment.succeeded / payment.canceled).
 * Тело уведомления не доверяем: статус перепроверяется запросом в API ЮKassa.
 * Настройка: личный кабинет ЮKassa → Интеграция → HTTP-уведомления → URL /yookassa/webhook.
 */
class YooKassaWebhookController extends Controller
{
    public function __invoke(Request $request, YooKassaService $yookassa)
    {
        $event = $request->input('event');
        $id    = $request->input('object.id');

        if (! in_array($event, ['payment.succeeded', 'payment.canceled'], true) || ! is_string($id) || $id === '') {
            return response('OK', 200);
        }

        try {
            $premium = PremiumPayment::where('gateway', 'yookassa')->where('external_id', $id)->first();

            if ($premium) {
                $yookassa->settlePremium($premium);

                return response('OK', 200);
            }

            $boost = BoostPayment::where('gateway', 'yookassa')->where('external_id', $id)->first();

            if ($boost) {
                $yookassa->settleBoost($boost);
            }
        } catch (\Throwable $e) {
            report($e);

            // 500 — ЮKassa повторит уведомление позже
            return response('error', 500);
        }

        return response('OK', 200);
    }
}
