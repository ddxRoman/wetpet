<?php

namespace App\Filament\Resources\ReviewReceiptResource\Pages;

use App\Filament\Resources\ReviewReceiptResource;
use Filament\Resources\Pages\EditRecord;

class EditReviewReceipt extends EditRecord
{
    protected static string $resource = ReviewReceiptResource::class;

    protected function afterSave(): void
    {
        // 🔁 После сохранения синхронизируем статус в reviews
        $review = $this->record->review;
        if ($review) {
            $review->update([
                'receipt_verified' => $this->record->status,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        // 🔙 После сохранения возвращаем на список
        return $this->getResource()::getUrl('index');
    }
}
