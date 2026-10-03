<?php

namespace App\Filament\Resources\ReviewDisputeResource\Pages;

use App\Filament\Resources\ReviewDisputeResource;
use Filament\Resources\Pages\ListRecords;

class ListReviewDisputes extends ListRecords
{
    protected static string $resource = ReviewDisputeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
