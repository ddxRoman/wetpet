<?php

namespace App\Filament\Resources\ClinicOwnerResource\Pages;

use App\Filament\Resources\ClinicOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClinicOwner extends EditRecord
{
    protected static string $resource = ClinicOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
