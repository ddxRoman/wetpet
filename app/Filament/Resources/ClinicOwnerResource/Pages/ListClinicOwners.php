<?php

namespace App\Filament\Resources\ClinicOwnerResource\Pages;

use App\Filament\Resources\ClinicOwnerResource;
use Filament\Resources\Pages\ListRecords;

class ListClinicOwners extends ListRecords
{
    protected static string $resource = ClinicOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
