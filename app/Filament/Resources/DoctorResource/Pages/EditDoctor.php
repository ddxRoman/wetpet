<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;



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

        // Места работы уже записаны в сводную таблицу — выравниваем основное место
        // (колонка clinic_id) с их списком.
        $this->record->syncWorkplaces($this->record->clinics()->pluck('clinics.id')->all());
    }
}