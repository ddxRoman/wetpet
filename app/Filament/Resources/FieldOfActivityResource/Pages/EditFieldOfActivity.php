<?php

namespace App\Filament\Resources\FieldOfActivityResource\Pages;

use App\Filament\Resources\FieldOfActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFieldOfActivity extends EditRecord
{
    protected static string $resource = FieldOfActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FieldOfActivityResource::prepareDataForSave($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
