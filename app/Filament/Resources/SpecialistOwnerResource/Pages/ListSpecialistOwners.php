<?php

namespace App\Filament\Resources\SpecialistOwnerResource\Pages;

use App\Filament\Resources\SpecialistOwnerResource;
use Filament\Resources\Pages\ListRecords;

class ListSpecialistOwners extends ListRecords
{
    protected static string $resource = SpecialistOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
