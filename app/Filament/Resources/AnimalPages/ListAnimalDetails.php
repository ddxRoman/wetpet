<?php
namespace App\Filament\Resources\AnimalPages;
use App\Filament\Resources\AnimalPages\AnimalDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnimalDetails extends ListRecords
{
    protected static string $resource = AnimalDetailResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}