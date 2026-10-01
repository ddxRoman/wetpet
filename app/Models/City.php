<?php

namespace App\Models;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class City extends Model
{
    protected $fillable = ['name','slug','region','country','large_city','verified','user_id'];
    // app/Models/City.php
public function users()
{
    return $this->hasMany(User::class);
}


    protected $casts = [
        'large_city' => 'boolean',
    ];

    protected static function booted()
    {
        // Новый непроверенный город — уведомление админам в Filament (как для врачей и специалистов).
        // Из консоли (импорт, сидеры) не шлём, чтобы не засыпать админов.
        static::created(function (City $city) {
            if ($city->verified !== 'unconfirmed' || app()->runningInConsole()) {
                return;
            }

            try {
                foreach (User::where('is_admin', true)->get() as $admin) {
                    Notification::make()
                        ->title('Новый населённый пункт')
                        ->body("«{$city->name}», {$city->region}. Требуется проверка.")
                        ->icon('heroicon-o-map-pin')
                        ->actions([
                            Action::make('view')
                                ->label('Открыть')
                                ->url(route('filament.admin.resources.cities.edit', $city))
                                ->button(),
                        ])
                        ->sendToDatabase($admin);
                }
            } catch (\Throwable $e) {
                Log::warning('Не удалось уведомить админов о новом городе: ' . $e->getMessage());
            }
        });
    }
}
