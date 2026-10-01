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
        // Раньше здесь стояло стандартное DeleteAction: оно удаляло только карточку (animal_details),
        // а сама порода в animals оставалась. Теперь — то же удаление породы целиком, что и в списке:
        // если на породу ссылаются питомцы/объявления/отзывы, просим выбрать породу-замену.
        return [
            Actions\Action::make('delete_breed')
                ->label('Удалить породу')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->modalHeading(fn () => AnimalDetailResource::hasDependents($this->record->animal)
                    ? 'У породы есть связанные записи'
                    : 'Удалить породу?')
                ->modalDescription(fn () => AnimalDetailResource::hasDependents($this->record->animal)
                    ? AnimalDetailResource::dependentsSummary($this->record->animal) . ' Выберите породу, на которую перенести эти записи перед удалением.'
                    : 'Порода и её карточка будут удалены безвозвратно. Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Удалить')
                ->form(fn () => AnimalDetailResource::hasDependents($this->record->animal)
                    ? [
                        \Filament\Forms\Components\Select::make('replacement_animal_id')
                            ->label('Перенести записи на породу')
                            ->helperText('Питомцы, объявления и отзывы этой породы будут привязаны к выбранной.')
                            ->options(fn () => Animal::query()
                                ->when($this->record->animal, fn ($q) => $q->where('id', '!=', $this->record->animal->id))
                                ->orderBy('species')->orderBy('breed')
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->species} — {$a->breed}"]))
                            ->searchable()
                            ->required(),
                    ]
                    : [])
                ->action(function (array $data) {
                    AnimalDetailResource::deleteOrMergeAnimal($this->record->animal, $data['replacement_animal_id'] ?? null);

                    // Если породу не удалось удалить (нет замены и т. п.) — остаёмся на странице
                    if (! \App\Models\AnimalDetail::whereKey($this->record->getKey())->exists()) {
                        $this->redirect($this->getResource()::getUrl('index'));
                    }
                }),
        ];
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