<?php

namespace App\Filament\Resources\OwnerVerificationResource\Pages;

use App\Filament\Resources\OwnerVerificationResource;
use Filament\Resources\Pages\ListRecords;

class ListOwnerVerifications extends ListRecords
{
    protected static string $resource = OwnerVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
