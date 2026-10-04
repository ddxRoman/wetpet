<?php

namespace App\Support;

use App\Models\ClinicOwner;
use App\Models\DoctorOwner;
use App\Models\OrganizationOwner;
use App\Models\SpecialistOwner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Единое место, где решается: «можно ли этому пользователю управлять карточкой».
 *
 * Управлять карточкой (клиникой, организацией, врачом, специалистом) может ТОЛЬКО подтверждённый
 * владелец (is_confirmed = true) или администратор. Пока заявка на владение не подтверждена
 * админом, у пользователя есть лишь доступ к странице заявки: загрузка документов и чат с админом.
 */
class OwnerAccess
{
    /** Тип карточки => [модель владельца, колонка с id карточки]. */
    private const MAP = [
        'clinic'       => [ClinicOwner::class,       'clinic_id'],
        'organization' => [OrganizationOwner::class, 'organization_id'],
        'doctor'       => [DoctorOwner::class,       'doctor_id'],
        'specialist'   => [SpecialistOwner::class,   'specialist_id'],
    ];

    /** Кэш на время запроса: "тип:id:user" => [есть заявка, подтверждена]. */
    private static array $cache = [];

    /** Подтверждённый владелец карточки (или админ). */
    public static function isConfirmedOwner(?User $user, string $type, int $id): bool
    {
        if (! $user) {
            return false;
        }

        if (($user->is_admin ?? false) === true) {
            return true;
        }

        return self::status($user->id, $type, $id)[1];
    }

    /** У пользователя есть заявка на эту карточку (подтверждённая или нет) — ему можно показать страницу заявки. */
    public static function hasClaim(?User $user, string $type, int $id): bool
    {
        return $user && self::status($user->id, $type, $id)[0];
    }

    /** Остановить запрос (403), если пользователь не подтверждённый владелец. */
    public static function authorizeConfirmed(string $type, int $id, ?string $message = null): void
    {
        abort_unless(
            self::isConfirmedOwner(Auth::user(), $type, $id),
            403,
            $message ?? 'Управлять карточкой может только подтверждённый владелец. Права появятся после проверки заявки администратором.'
        );
    }

    /** Остановить запрос (403), если у пользователя нет даже заявки на эту карточку. */
    public static function authorizeClaimant(string $type, int $id, ?string $message = null): void
    {
        abort_unless(
            self::hasClaim(Auth::user(), $type, $id) || (Auth::user()?->is_admin ?? false) === true,
            403,
            $message ?? 'У вас нет прав для управления этим объектом.'
        );
    }

    /** @return array{0: bool, 1: bool} [есть заявка, заявка подтверждена] */
    private static function status(int $userId, string $type, int $id): array
    {
        if (! isset(self::MAP[$type])) {
            return [false, false];
        }

        $key = "{$type}:{$id}:{$userId}";

        if (! isset(self::$cache[$key])) {
            [$model, $column] = self::MAP[$type];

            $row = $model::where('user_id', $userId)
                ->where($column, $id)
                ->orderByDesc('is_confirmed')
                ->first(['id', 'is_confirmed']);

            self::$cache[$key] = [(bool) $row, (bool) ($row?->is_confirmed)];
        }

        return self::$cache[$key];
    }
}
