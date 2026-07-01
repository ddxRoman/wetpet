<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\Organization;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\Promotion;
use App\Models\User;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('promotions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Выдаём рекламный пакет первому пользователю (для теста)
        $promoUser = User::first();
        $promoUser?->update([
            'has_promo_package'        => true,
            'promo_package_expires_at' => now()->addYear()->toDateString(),
        ]);

        // ⚠️ ВНИМАНИЕ: ниже мы массово проставляем created_by всем сущностям —
        // это годится только для ДЕМО-окружения. На проде НЕ запускайте этот блок,
        // там created_by уже должен быть выставлен реальными владельцами.
        if ($promoUser) {
            Clinic::query()->update(['created_by' => $promoUser->id]);
            Organization::query()->update(['created_by' => $promoUser->id]);
            Doctor::query()->update(['created_by' => $promoUser->id]);
            Specialist::query()->update(['created_by' => $promoUser->id]);
        }

        // ── Акции клиник ─────────────────────────────────────────────────
        $clinicPromos = [
            [
                'name'   => 'Биосфера',
                'promos' => [
                    [
                        'title'       => 'Комплексная вакцинация',
                        'description' => 'Вакцинация от 5 основных заболеваний + осмотр в подарок',
                        'old_price'   => 2500,
                        'new_price'   => 1800,
                        'badge'       => '-28%',
                        'expires_at'  => now()->addDays(30)->toDateString(),
                    ],
                    [
                        'title'       => 'УЗИ брюшной полости',
                        'description' => 'Полное УЗИ-обследование органов брюшной полости',
                        'old_price'   => 1800,
                        'new_price'   => 1350,
                        'badge'       => '-25%',
                        'expires_at'  => now()->addDays(14)->toDateString(),
                    ],
                ],
            ],
            [
                'name'   => 'Добрый доктор',
                'promos' => [
                    [
                        'title'       => 'Чек-ап «Здоровый котёнок»',
                        'description' => 'Комплексный осмотр, анализы крови и мочи для котят до 1 года',
                        'old_price'   => 4000,
                        'new_price'   => 2900,
                        'badge'       => 'Топ цена',
                        'expires_at'  => now()->addDays(21)->toDateString(),
                    ],
                ],
            ],
        ];

        foreach ($clinicPromos as $item) {
            $clinic = Clinic::where('name', 'like', '%' . $item['name'] . '%')->first();
            if (!$clinic) continue;

            foreach ($item['promos'] as $promo) {
                Promotion::create([
                    'promotable_type' => Clinic::class,
                    'promotable_id'   => $clinic->id,
                    'title'           => $promo['title'],
                    'description'     => $promo['description'] ?? null,
                    'old_price'       => $promo['old_price'] ?? null,
                    'new_price'       => $promo['new_price'] ?? null,
                    'badge'           => $promo['badge'] ?? null,
                    'expires_at'      => $promo['expires_at'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        // ── Акции организаций ─────────────────────────────────────────────
        $orgPromos = [
            [
                'name'   => 'Краснодог',
                'promos' => [
                    [
                        'title'       => 'Комплекс груминга для Йорка',
                        'description' => 'Стрижка, купание, сушка, чистка ушей и когтей',
                        'old_price'   => 2200,
                        'new_price'   => 1650,
                        'badge'       => '-30%',
                        'expires_at'  => now()->addDays(20)->toDateString(),
                    ],
                    [
                        'title'       => 'Экспресс-линька кошек',
                        'description' => 'Вычёсывание подшёрстка, купание, сушка, укладка',
                        'old_price'   => 2000,
                        'new_price'   => 1500,
                        'badge'       => 'Скидка',
                        'expires_at'  => now()->addDays(10)->toDateString(),
                    ],
                ],
            ],
            [
                'name'   => 'Gavvashop',
                'promos' => [
                    [
                        'title'       => 'УЗ-чистка зубов',
                        'description' => 'Профессиональная чистка ультразвуком без анестезии',
                        'old_price'   => 3500,
                        'new_price'   => 2490,
                        'badge'       => 'Топ цена',
                        'expires_at'  => now()->addDays(25)->toDateString(),
                    ],
                ],
            ],
        ];

        foreach ($orgPromos as $item) {
            $org = Organization::where('name', 'like', '%' . $item['name'] . '%')->first();
            if (!$org) continue;

            foreach ($item['promos'] as $promo) {
                Promotion::create([
                    'promotable_type' => Organization::class,
                    'promotable_id'   => $org->id,
                    'title'           => $promo['title'],
                    'description'     => $promo['description'] ?? null,
                    'old_price'       => $promo['old_price'] ?? null,
                    'new_price'       => $promo['new_price'] ?? null,
                    'badge'           => $promo['badge'] ?? null,
                    'expires_at'      => $promo['expires_at'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        // ── Акции специалистов ────────────────────────────────────────────
        $specialistPromos = [
            [
                'name'   => 'Симонян',
                'promos' => [
                    [
                        'title'       => 'Первичный приём со скидкой',
                        'description' => 'Первичный осмотр + консультация по питанию',
                        'old_price'   => 1500,
                        'new_price'   => 900,
                        'badge'       => '-40%',
                        'expires_at'  => now()->addDays(15)->toDateString(),
                    ],
                ],
            ],
            [
                'name'   => 'Золотарёва',
                'promos' => [
                    [
                        'title'       => 'Выезд на дом — выгодно',
                        'description' => 'Выезд на дом в пределах города по сниженной цене',
                        'old_price'   => 2000,
                        'new_price'   => 1400,
                        'badge'       => '-30%',
                        'expires_at'  => now()->addDays(20)->toDateString(),
                    ],
                ],
            ],
        ];

        foreach ($specialistPromos as $item) {
            $specialist = Specialist::where('name', 'like', '%' . $item['name'] . '%')->first();
            if (!$specialist) continue;

            foreach ($item['promos'] as $promo) {
                Promotion::create([
                    'promotable_type' => Specialist::class,
                    'promotable_id'   => $specialist->id,
                    'title'           => $promo['title'],
                    'description'     => $promo['description'] ?? null,
                    'old_price'       => $promo['old_price'] ?? null,
                    'new_price'       => $promo['new_price'] ?? null,
                    'badge'           => $promo['badge'] ?? null,
                    'expires_at'      => $promo['expires_at'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        // ── Акции докторов ────────────────────────────────────────────────
        $doctorPromos = [
            [
                'name'   => 'Арыныч',
                'promos' => [
                    [
                        'title'       => 'Приём + анализы',
                        'description' => 'Осмотр + общий анализ крови и мочи в одном визите',
                        'old_price'   => 2800,
                        'new_price'   => 1990,
                        'badge'       => 'Комплекс',
                        'expires_at'  => now()->addDays(18)->toDateString(),
                    ],
                ],
            ],
        ];

        foreach ($doctorPromos as $item) {
            $doctor = Doctor::where('name', 'like', '%' . $item['name'] . '%')->first();
            if (!$doctor) continue;

            foreach ($item['promos'] as $promo) {
                Promotion::create([
                    'promotable_type' => Doctor::class,
                    'promotable_id'   => $doctor->id,
                    'title'           => $promo['title'],
                    'description'     => $promo['description'] ?? null,
                    'old_price'       => $promo['old_price'] ?? null,
                    'new_price'       => $promo['new_price'] ?? null,
                    'badge'           => $promo['badge'] ?? null,
                    'expires_at'      => $promo['expires_at'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        $this->command->info('✅ PromotionSeeder: создано ' . Promotion::count() . ' акций');
    }
}