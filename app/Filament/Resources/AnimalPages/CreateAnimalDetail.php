<?php
namespace App\Filament\Resources\AnimalPages;

use App\Filament\Resources\AnimalPages\AnimalDetailResource;
use App\Models\Animal;
use Filament\Resources\Pages\CreateRecord;

class CreateAnimalDetail extends CreateRecord
{
    protected static string $resource = AnimalDetailResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Поля 'species' и 'breed_name' не относятся к таблице animal_details —
        // они нужны, чтобы создать связанную запись в animals.
        $animal = Animal::create([
            'species' => $data['species'],
            'breed' => $data['breed_name'],
        ]);

        $data['animal_breed'] = $animal->id;

        unset($data['species'], $data['breed_name']);

        return $data;
    }
}