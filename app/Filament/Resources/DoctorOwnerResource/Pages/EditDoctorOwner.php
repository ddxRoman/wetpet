<?php

namespace App\Filament\Resources\DoctorOwnerResource\Pages;

use App\Filament\Resources\DoctorOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDoctorOwner extends EditRecord
{
    protected static string $resource = DoctorOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
