<?php

namespace App\Filament\Resources\SpecialistOwnerResource\Pages;

use App\Filament\Resources\SpecialistOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpecialistOwner extends EditRecord
{
    protected static string $resource = SpecialistOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
