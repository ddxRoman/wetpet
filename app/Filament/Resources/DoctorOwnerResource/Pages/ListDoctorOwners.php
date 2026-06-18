<?php

namespace App\Filament\Resources\DoctorOwnerResource\Pages;

use App\Filament\Resources\DoctorOwnerResource;
use Filament\Resources\Pages\ListRecords;

class ListDoctorOwners extends ListRecords
{
    protected static string $resource = DoctorOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
