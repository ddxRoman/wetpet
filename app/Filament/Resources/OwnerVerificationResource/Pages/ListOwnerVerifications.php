<?php

namespace App\Filament\Resources\OwnerVerificationResource\Pages;

use App\Filament\Resources\OwnerVerificationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListOwnerVerifications extends ListRecords
{
    protected static string $resource = OwnerVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Таблица строится из UNION четырёх таблиц владельцев (clinic_owners,
     * organization_owners, doctor_owners, specialist_owners) во вложенном запросе
     * `owner_requests`. Отсюда две проблемы:
     *
     *  1. id в разных таблицах повторяются (clinic_owners.id = 1 и organization_owners.id = 1),
     *     поэтому ключом строки должна быть пара «тип-id», а не один id;
     *  2. Filament по умолчанию ищет запись через ->find($key), то есть по колонке
     *     `clinic_owners`.`id`, а во внешнем запросе такой таблицы нет — есть только
     *     `owner_requests`. Отсюда ошибка «Unknown column 'clinic_owners.id'» (500)
     *     при нажатии на любое действие в строке.
     */
    public function getTableRecordKey(Model $record): string
    {
        $type = $record->getAttribute('entity_type');

        return filled($type)
            ? $type . '-' . $record->getKey()
            : (string) $record->getKey();
    }

    protected function resolveTableRecord(?string $key): ?Model
    {
        if ($key === null) {
            return null;
        }

        $query = $this->getFilteredTableQuery();

        // Колонки не квалифицируем таблицей: во внешнем запросе она одна — owner_requests.
        if (str_contains($key, '-')) {
            [$type, $id] = explode('-', $key, 2);

            return $query->where('entity_type', $type)->where('id', $id)->first();
        }

        return $query->where('id', $key)->first();
    }
}
