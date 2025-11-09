<?php

namespace App\Filament\Resources\ReviewReceiptResource\Pages;

use App\Filament\Resources\ReviewReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewReceipts extends ListRecords
{
    protected static string $resource = ReviewReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
