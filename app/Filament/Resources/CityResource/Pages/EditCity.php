<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCity extends EditRecord
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    // Слаг участвует в URL — меняем его только если изменилось название
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = CityResource::prepareRegion($data);

        if ($data['name'] !== $this->record->name) {
            $data['slug'] = CityResource::makeSlug($data['name'], $this->record->id);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
