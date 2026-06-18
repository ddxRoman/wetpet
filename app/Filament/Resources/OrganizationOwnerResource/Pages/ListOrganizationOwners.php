<?php

namespace App\Filament\Resources\OrganizationOwnerResource\Pages;

use App\Filament\Resources\OrganizationOwnerResource;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationOwners extends ListRecords
{
    protected static string $resource = OrganizationOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
