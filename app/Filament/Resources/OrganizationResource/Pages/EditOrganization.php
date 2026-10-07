<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use App\Models\Organization;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

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
                ->modalDescription('Будет создана новая запись организации с тем же названием, городом/регионом/страной, логотипом и сферой деятельности (улица и дом не копируются), и откроется её редактирование. Копия берётся из СОХРАНЁННЫХ данных — несохранённые правки на этой странице в неё не попадут.')
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

        $branch = new Organization();
        $branch->fill(Arr::only($source->getAttributes(), ['name', 'country', 'region', 'city', 'field_of_activity_id']));

        // Колонка street в БД NOT NULL без значения по умолчанию, поэтому у филиала
        // улица пустая строка (заполняется при редактировании); дом — nullable.
        $branch->street = '';

        // Логотип копируем отдельным файлом, чтобы удаление/замена у одной записи не ломали другую.
        if ($source->logo && Storage::disk('public')->exists($source->logo)) {
            $extension = pathinfo($source->logo, PATHINFO_EXTENSION);
            $newPath   = 'organizations/logos/' . uniqid('branch_', true) . ($extension ? '.' . $extension : '');

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

        $this->redirect(OrganizationResource::getUrl('edit', ['record' => $branch]));
    }
}
