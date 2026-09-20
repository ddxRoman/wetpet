<?php

namespace App\Filament\Resources\SpecialistResource\Pages;

use App\Filament\Resources\SpecialistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialist extends EditRecord
{
    protected static string $resource = SpecialistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Синхронизируем старую строковую колонку 'specialization' (через запятую),
        // от неё зависят поиск, подбор услуг, SEO-шаблоны и уведомления.
        $this->record->update([
            'specialization' => $this->record->specializations()->pluck('name')->implode(', '),
        ]);
    }
}
