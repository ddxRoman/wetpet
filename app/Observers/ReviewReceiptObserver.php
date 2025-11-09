<?php

namespace App\Observers;

use App\Models\ReviewReceipt;

class ReviewReceiptObserver
{
    /**
     * При сохранении или обновлении статуса.
     */
    public function saved(ReviewReceipt $receipt): void
    {
 if ($receipt->review) {
            $receipt->review->update([
                'receipt_verified' => $receipt->status, // ✅ исправлено
            ]);
        }
    }

    /**
     * При удалении чека.
     */
    public function deleted(ReviewReceipt $receipt): void
    {
        if ($receipt->review) {
            $receipt->review->update([
                'receipt_verified' => false,
            ]);
        }
    }
}
