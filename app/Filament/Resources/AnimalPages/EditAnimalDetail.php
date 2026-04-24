<?php
namespace App\Filament\Resources\AnimalPages;
use App\Filament\Resources\AnimalPages\AnimalDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnimalDetail extends EditRecord
{
    protected static string $resource = AnimalDetailResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}