<?php

namespace App\Filament\Resources\SeoCatalogPageResource\Pages;

use App\Filament\Resources\SeoCatalogPageResource;
use Filament\Resources\Pages\EditRecord;

class EditSeoCatalogPage extends EditRecord
{
    protected static string $resource = SeoCatalogPageResource::class;

    protected function getHeaderActions(): array
    {
        // Удаление отключено — набор ключей фиксирован в коде.
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
