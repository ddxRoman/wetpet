<?php

namespace App\Filament\Resources\SeoCatalogPageResource\Pages;

use App\Filament\Resources\SeoCatalogPageResource;
use Filament\Resources\Pages\ListRecords;

class ListSeoCatalogPages extends ListRecords
{
    protected static string $resource = SeoCatalogPageResource::class;

    protected function getHeaderActions(): array
    {
        // Создание записей отключено — набор ключей фиксирован в коде.
        return [];
    }
}
