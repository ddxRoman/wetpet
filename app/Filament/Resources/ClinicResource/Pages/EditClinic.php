<?php

namespace App\Filament\Resources\ClinicResource\Pages;

use App\Filament\Resources\ClinicResource;
use App\Models\Clinic;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class EditClinic extends EditRecord
{
    protected static string $resource = ClinicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),

            Actions\Action::make('createBranch')
                ->label('Создать филиал')
                ->icon('heroicon-o-building-office-2')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Создать филиал')
                ->modalDescription('Будет создана новая запись клиники с тем же названием, городом/регионом/страной, логотипом и сферой деятельности (улица и дом не копируются), и откроется её редактирование. Копия берётся из СОХРАНЁННЫХ данных — несохранённые правки на этой странице в неё не попадут.')
                ->modalSubmitActionLabel('Создать и открыть')
                ->action(fn () => $this->createBranch()),
        ];
    }

    /**
     * Создаёт филиал: копия записи без улицы и дома — и сразу открывает его редактирование.
     * Создание идёт обычным save(), поэтому срабатывает EntityCreationObserver
     * (created_by = текущий админ, is_verified = true) и генерируется собственный slug.
     */
    protected function createBranch(): void
    {
        $source = $this->record;

        $branch = new Clinic();
        $branch->fill(Arr::only($source->getAttributes(), ['name', 'country', 'region', 'city']));

        // Колонка street в БД NOT NULL без значения по умолчанию, поэтому у филиала
        // улица пустая строка (заполняется при редактировании); дом — nullable.
        $branch->street = '';

        // Логотип копируем отдельным файлом, чтобы удаление/замена у одной записи не ломали другую.
        if ($source->logo && Storage::disk('public')->exists($source->logo)) {
            $extension = pathinfo($source->logo, PATHINFO_EXTENSION);
            $newPath   = 'clinics/logos/' . uniqid('branch_', true) . ($extension ? '.' . $extension : '');

            if (Storage::disk('public')->copy($source->logo, $newPath)) {
                $branch->logo = $newPath;
            }
        }

        $branch->save();

        Notification::make()
            ->title('Филиал создан')
            ->body('Заполните улицу, дом и остальные данные филиала.')
            ->success()
            ->send();

        $this->redirect(ClinicResource::getUrl('edit', ['record' => $branch]));
    }
}
