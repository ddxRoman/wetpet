<?php

namespace App\Filament\Resources\FieldOfActivityResource\Pages;

use App\Filament\Resources\FieldOfActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFieldOfActivities extends ListRecords
{
    protected static string $resource = FieldOfActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Добавить направление'),
        ];
    }
}
