<?php
namespace App\Filament\Resources\AnimalPages;

use App\Filament\Resources\AnimalPages\AnimalDetailResource;
use App\Models\Animal;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnimalDetail extends EditRecord
{
    protected static string $resource = AnimalDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $animal = $this->record->animal;

        if ($animal) {
            $data['species'] = $animal->species;
            $data['breed_name'] = $animal->breed;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $animal = $this->record->animal;

        if ($animal) {
            $animal->update([
                'species' => $data['species'],
                'breed' => $data['breed_name'],
            ]);
        } else {
            $animal = Animal::create([
                'species' => $data['species'],
                'breed' => $data['breed_name'],
            ]);

            $data['animal_breed'] = $animal->id;
        }

        unset($data['species'], $data['breed_name']);

        return $data;
    }
}