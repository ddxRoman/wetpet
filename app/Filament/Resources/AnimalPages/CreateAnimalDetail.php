<?php
namespace App\Filament\Resources\AnimalPages;
use App\Filament\Resources\AnimalPages\AnimalDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnimalDetail extends CreateRecord
{
    protected static string $resource = AnimalDetailResource::class;
}