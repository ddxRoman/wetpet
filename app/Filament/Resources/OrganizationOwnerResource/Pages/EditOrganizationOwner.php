<?php

namespace App\Filament\Resources\OrganizationOwnerResource\Pages;

use App\Filament\Resources\OrganizationOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationOwner extends EditRecord
{
    protected static string $resource = OrganizationOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
