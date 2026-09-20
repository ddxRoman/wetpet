<?php

namespace App\Filament\Resources\SpecialistResource\Pages;

use App\Filament\Resources\SpecialistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecialist extends CreateRecord
{
    protected static string $resource = SpecialistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['specialization'] = '';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Синхронизируем старую строковую колонку 'specialization' (через запятую),
        // от неё зависят поиск, подбор услуг, SEO-шаблоны и уведомления.
        $this->record->update([
            'specialization' => $this->record->specializations()->pluck('name')->implode(', '),
        ]);
    }
}
