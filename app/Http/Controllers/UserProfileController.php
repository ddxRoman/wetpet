<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Specialist;
use App\Models\User;
use App\Support\AgeFormatter;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserProfileController extends Controller
{
    /**
     * Публичный профиль пользователя: /user/{id}
     * Открывается по клику на имя автора в отзывах.
     */
    public function show($id)
    {
        // Заблокированных пользователей публично не показываем
        $profileUser = User::where('status', '!=', 'ban')->findOrFail($id);

        // Сколько времени прошло с регистрации (точно, без округления)
        $registeredFor = $profileUser->created_at
            ? (AgeFormatter::since($profileUser->created_at) ?: 'меньше суток')
            : null;

        // Питомцы
        $pets = $profileUser->pets()
            ->with('animal')
            ->orderBy('name')
            ->get()
            ->each(function ($pet) {
                if ($pet->birth_date) {
                    // Для умершего питомца — возраст на момент смерти, для живого — на сегодня
                    $label = AgeFormatter::since($pet->birth_date, false, $pet->death_date) ?: 'меньше месяца';
                } elseif ($pet->age) {
                    $label = $pet->age . ' ' . AgeFormatter::plural((int) $pet->age, 'год', 'года', 'лет');
                } else {
                    $label = null;
                }

                $pet->setAttribute('age_label', $label);
            });

        // Отзывы пользователя. Отзывы на удалённые объекты не выводим,
        // чтобы не получать null в ссылке и названии.
        $reviews = Review::where('user_id', $profileUser->id)
            ->whereHasMorph('reviewable', [
                Clinic::class,
                Doctor::class,
                Specialist::class,
                Organization::class,
            ])
            ->with([
                // Для адреса нужны город специалиста и клиника врача
                'reviewable' => fn (MorphTo $morph) => $morph->morphWith([
                    Doctor::class     => ['clinic'],
                    Specialist::class => ['city'],
                ]),
                'photos',
                'pet.animal',
            ])
            ->orderByDesc('review_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Адрес объекта, на который написан отзыв (выводится под названием)
        $reviews->getCollection()->each(function ($review) {
            $t = $review->reviewable;

            $address = match (true) {
                $t instanceof Clinic,
                $t instanceof Organization => $this->formatAddress($t->city, $t->street, $t->house),
                $t instanceof Specialist   => $this->formatAddress($t->city?->name, $t->street, $t->house),
                // У врача своего адреса нет — берём адрес его клиники
                $t instanceof Doctor       => $t->clinic
                    ? $this->formatAddress($t->clinic->city, $t->clinic->street, $t->clinic->house)
                    : null,
                default                    => null,
            };

            $review->setAttribute('target_address', $address);
        });

        $seoMeta = [
            'title'          => 'Профиль ' . $profileUser->name . ' — Зверозор',
            'description'    => 'Профиль пользователя ' . $profileUser->name . ': питомцы и отзывы о ветеринарных клиниках и врачах.',
            'og_title'       => 'Профиль ' . $profileUser->name . ' — Зверозор',
            'og_description' => 'Питомцы и отзывы пользователя ' . $profileUser->name,
            'robots'         => 'noindex, follow',
        ];

        return view('pages.user.profile', [
            'profileUser'   => $profileUser,
            'registeredFor' => $registeredFor,
            'pets'          => $pets,
            'reviews'       => $reviews,
            'seoMeta'       => $seoMeta,
        ]);
    }

    /**
     * «Город, улица дом». Пустые части пропускаются; если ничего нет — null.
     */
    private function formatAddress($city, $street, $house): ?string
    {
        $streetHouse = trim(implode(' ', array_filter([trim((string) $street), trim((string) $house)])));
        $parts = array_filter([trim((string) $city), $streetHouse]);

        return $parts ? implode(', ', $parts) : null;
    }
}