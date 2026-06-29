<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

/**
 * Общий observer для Doctor / Organization / Clinic / Specialist.
 *
 * Логика модерации:
 *   - Запись создана обычным пользователем через публичный сайт
 *     (Auth::check() === true и пользователь НЕ администратор)
 *     → is_verified = false, нужна проверка.
 *   - Все остальные случаи — создано администратором через Filament,
 *     через сидер, artisan tinker, консольную команду, или вообще без
 *     авторизации (Auth::check() === false, например CLI-контекст)
 *     → is_verified = true, модерация не требуется.
 */
class EntityCreationObserver
{
    public function creating(Model $model): void
    {
        $creator = Auth::check() ? Auth::user() : null;

        if ($creator && !$model->created_by) {
            $model->created_by = $creator->id;
        }

        if (!$model->exists) {
            // На модерацию уходит только то, что реально создал залогиненный
            // обычный пользователь с сайта. Всё остальное (сидер, консоль,
            // tinker, сам админ) считается доверенным и не требует проверки.
            $isPublicUserSubmission = $creator !== null && !$creator->isAdmin();

            $model->is_verified = !$isPublicUserSubmission;
        }
    }

    public function created(Model $model): void
    {
        // Если запись уже проверена — уведомлять админов не нужно
        if ($model->is_verified) {
            return;
        }

        $entityLabel = match (get_class($model)) {
            \App\Models\Doctor::class       => 'Врач',
            \App\Models\Organization::class => 'Организация',
            \App\Models\Clinic::class       => 'Клиника',
            \App\Models\Specialist::class   => 'Специалист',
            default                          => 'Запись',
        };

        $resourceUrl = match (get_class($model)) {
            \App\Models\Doctor::class       => route('filament.admin.resources.doctors.edit', $model),
            \App\Models\Organization::class => route('filament.admin.resources.organizations.edit', $model),
            \App\Models\Clinic::class       => route('filament.admin.resources.clinics.edit', $model),
            \App\Models\Specialist::class   => route('filament.admin.resources.specialists.edit', $model),
            default                          => null,
        };

        $creatorName = $model->created_by
            ? (User::find($model->created_by)?->name ?? 'неизвестный пользователь')
            : 'неизвестный пользователь';

        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title("Новая запись: {$entityLabel}")
                ->body("«{$model->name}» добавил(а) {$creatorName}. Требуется проверка.")
                ->icon('heroicon-o-bell-alert')
                ->actions($resourceUrl ? [
                    Action::make('view')
                        ->label('Открыть')
                        ->url($resourceUrl)
                        ->button(),
                ] : [])
                ->sendToDatabase($admin);
        }
    }
}
