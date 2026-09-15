<?php

namespace App\Filament\Resources\FieldOfActivityResource\Pages;

use App\Filament\Resources\FieldOfActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFieldOfActivity extends CreateRecord
{
    protected static string $resource = FieldOfActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FieldOfActivityResource::prepareDataForSave($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
