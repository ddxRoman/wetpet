<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = CityResource::prepareRegion($data);

        $data['slug'] = CityResource::makeSlug($data['name']);
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
