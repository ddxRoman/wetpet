@php
use App\Models\City;
use App\Models\Promotion;
use App\Models\Clinic;
use App\Models\Organization;
use App\Models\Doctor;
use App\Models\Specialist;

/*
 | Блок «Выгодные предложения» на главной.
 |  • акции показываются только если регион карточки совпадает с регионом выбранного пользователем города;
 |  • только акции с галочкой «Выводить на главной», не более 3 от одной карточки;
 |  • в блоке максимум 15 акций: 3 столбца по 5 штук; если подходящих больше — случайная выборка.
 */
$userCity = auth()->user()?->city
    ?? (session('city_id') ? City::find(session('city_id')) : null)
    ?? (session('city_name') ? City::where('name', session('city_name'))->first() : null);

$region = $userCity && filled($userCity->region) ? mb_strtolower(trim($userCity->region)) : null;

$promos = collect();

if ($region) {
    $candidates = Promotion::with('promotable')
        ->active()
        ->forHome()
        ->where(function ($outer) use ($region) {
            $outer->whereHasMorph('promotable', [Clinic::class, Organization::class],
                    fn ($q) => $q->inUserRegion($region))
                ->orWhereHasMorph('promotable', [Doctor::class, Specialist::class],
                    fn ($q) => $q->whereHas('city', fn ($c) => $c->whereRaw('LOWER(TRIM(region)) = ?', [$region])));
        })
        ->orderByDesc('id')
        ->get()
        ->filter(fn ($p) => $p->promotable);

    // Не более 3 акций от одной карточки
    $candidates = $candidates
        ->groupBy(fn ($p) => $p->promotable_type . ':' . $p->promotable_id)
        ->flatMap(fn ($group) => $group->take(Promotion::HOME_PER_CARD))
        ->values();

    // Больше 15 — случайная выборка из подходящих
    $promos = $candidates->count() > Promotion::HOME_TOTAL
        ? $candidates->shuffle()->take(Promotion::HOME_TOTAL)->values()
        : $candidates;
}

// Раскладка: 3 столбца, в каждом до 5 акций (при малом числе акций делим поровну)
$perColumn = max(1, min(5, (int) ceil($promos->count() / 3)));
$columns   = $promos->chunk($perColumn);

$routeMap = [
    'Clinic'       => 'clinics.show',
    'Organization' => 'organizations.show',
    'Doctor'       => 'doctors.show',
    'Specialist'   => 'specialists.show',
];
@endphp

@if($promos->isNotEmpty())
<style>
.promo-home-cols{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:start;}
.promo-home-col{display:flex;flex-direction:column;gap:8px;min-width:0;}

.promo-mini{position:relative;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;column-gap:10px;padding:8px 12px 8px 15px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#212529;min-width:0;transition:transform .15s ease,box-shadow .15s ease;overflow:hidden;}
.promo-mini::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:#fd7e14;}
.promo-home-col:nth-child(2) .promo-mini::before{background:#8dc63f;}
.promo-home-col:nth-child(3) .promo-mini::before{background:#0d6efd;}
.promo-mini:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(0,0,0,.08);color:#212529;}
.promo-mini__info{display:flex;flex-direction:column;gap:2px;min-width:0;}
.promo-mini__title{font-size:.85rem;font-weight:600;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.promo-mini__org{font-size:.72rem;color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.promo-mini__side{display:flex;flex-direction:column;align-items:flex-end;gap:2px;}
.promo-mini__chip{background:#dc3545;color:#fff;font-size:.62rem;font-weight:700;line-height:1;padding:3px 6px;border-radius:6px;white-space:nowrap;}
.promo-mini__prices{display:flex;align-items:baseline;gap:5px;white-space:nowrap;}
.promo-mini__old{font-size:.7rem;color:#9ca3af;text-decoration:line-through;}
.promo-mini__new{font-size:.9rem;font-weight:700;color:#16a34a;}

@media(max-width:992px){
    .promo-home-wrapper{padding:0 15px 20px;}
    .promo-home-cols{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;gap:10px;padding:4px 2px 12px;scrollbar-width:none;-ms-overflow-style:none;}
    .promo-home-cols::-webkit-scrollbar{display:none;}
    .promo-home-col{display:contents;}
    .promo-mini{flex:0 0 78vw;max-width:300px;scroll-snap-align:start;}
}
</style>

<div class="container promo-home-wrapper my-4">
    <h2 class="header_h2 mb-3">Выгодные предложения в г. {{ $currentCityName }}</h2>

    <div class="promo-home-cols">
        @foreach($columns as $colPromos)
        <div class="promo-home-col">
            @foreach($colPromos as $promo)
                @php
                    $entity  = $promo->promotable;
                    $typeStr = class_basename($promo->promotable_type);
                    $url     = '#';

                    if (isset($routeMap[$typeStr]) && $entity) {
                        $routeParams = match ($typeStr) {
                            'Clinic'       => ['city' => $entity->city_slug, 'clinic' => $entity->slug],
                            'Organization' => ['city' => $entity->city_slug, 'slug' => $entity->slug],
                            default        => ($entity->slug ?? '#'),
                        };
                        $url = route($routeMap[$typeStr], $routeParams);
                    }

                    if ($promo->old_price && $promo->new_price && $promo->old_price > $promo->new_price) {
                        $chip = '−' . round((1 - $promo->new_price / $promo->old_price) * 100) . '%';
                    } else {
                        $chip = $promo->badge ?: '% Акция';
                    }
                @endphp

                <a href="{{ $url }}" class="promo-mini" title="{{ $promo->title }}">
                    <span class="promo-mini__info">
                        <span class="promo-mini__title">{{ $promo->title }}</span>
                        <span class="promo-mini__org">{{ $entity->name ?? '' }}</span>
                    </span>
                    <span class="promo-mini__side">
                        <span class="promo-mini__chip">{{ $chip }}</span>
                        @if($promo->old_price || $promo->new_price)
                            <span class="promo-mini__prices">
                                @if($promo->old_price)
                                    <span class="promo-mini__old">{{ number_format($promo->old_price, 0, '.', ' ') }} ₽</span>
                                @endif
                                @if($promo->new_price)
                                    <span class="promo-mini__new">{{ number_format($promo->new_price, 0, '.', ' ') }} ₽</span>
                                @endif
                            </span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endif
