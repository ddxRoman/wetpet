<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Toggle;

/**
 * Переключатель для строковых полей «Да/Нет» (exotic_animals, On_site_assistance у врачей и специалистов).
 *
 * В базе значение хранится как «Да» / «Нет» — так его читает сайт. Старые записи, сохранённые
 * из админки как yes/no или из кабинета как 1, тоже показываются включёнными.
 */
class YesNoToggle
{
    public static function make(string $name, string $label): Toggle
    {
        return Toggle::make($name)
            ->label($label)
            ->formatStateUsing(function ($state): bool {
                if (is_string($state)) {
                    return in_array(mb_strtolower(trim($state)), ['да', 'yes', '1', 'true'], true);
                }

                return (bool) $state;
            })
            ->dehydrateStateUsing(fn ($state) => $state ? 'Да' : 'Нет');
    }
}
