@php
use App\Models\Promotion;
use App\Models\Clinic;
use App\Models\Organization;
use App\Models\Doctor;
use App\Models\Specialist;

$cityId = $currentCityId ?? null;

$promos = Promotion::with('promotable')
    ->active()
    ->whereHasMorph('promotable', [Clinic::class, Organization::class, Doctor::class, Specialist::class], function ($q) use ($cityId) {
        if ($cityId) {
            $q->where('city_id', $cityId);
        }
    })
    ->latest()
    ->take(6)
    ->get();

$columns  = $promos->chunk(2);
$hasPromos = $promos->isNotEmpty();

$colTitles = ['Скидки на услуги', 'Спецпредложения', 'Акции партнёров'];
$routeMap  = [
    'Clinic'       => 'clinics.show',
    'Organization' => 'organizations.show',
    'Doctor'       => 'doctors.show',
    'Specialist'   => 'specialists.show',
];
@endphp

@if($hasPromos)
<style>
.badge-discount{background-color:#dc3545;color:white;font-size:.75rem;font-weight:700;padding:3px 7px;border-radius:6px;}
.commercial-old-price{text-decoration:line-through;color:#999;font-size:.85rem;margin-right:5px;}
.commercial-new-price{color:#28a745;font-weight:700;}
.btn-commercial{font-size:.85rem;padding:5px 12px;border-radius:8px;font-weight:500;transition:all .2s ease;white-space:nowrap;}
.commercial-card-desktop{background:#fff;border-radius:12px;padding:15px;transition:transform .2s ease,box-shadow .2s ease;height:100%;}
.commercial-card-desktop:hover{transform:translateY(-3px);box-shadow:0 5px 15px rgba(0,0,0,.08);}

@media(min-width:993px){.mobile-popular-wrapper{display:none!important;}}

@media(max-width:992px){
    .desktop-popular-wrapper{display:none!important;}
    .mobile-popular-wrapper{padding:0 15px 20px;}
    .mobile-popular-title{font-size:1.2rem;font-weight:700;color:#222;margin-bottom:12px;}
    .mobile-popular-scroll{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;gap:12px;padding:4px 2px 12px;scrollbar-width:none;-ms-overflow-style:none;}
    .mobile-popular-scroll::-webkit-scrollbar{display:none;}
    .mobile-popular-card{flex:0 0 82vw;max-width:300px;background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);border:1px solid #f0f0f0;overflow:hidden;scroll-snap-align:start;}
    .mobile-popular-card-header{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#f8f9fa;border-bottom:1px solid #eee;font-weight:600;font-size:.88rem;color:#333;}
    .mobile-popular-item{padding:10px 14px;border-bottom:1px solid #f3f4f6;}
    .mobile-popular-item:last-child{border-bottom:none;}
    .mobile-popular-link{display:block;text-decoration:none;color:#212529;font-size:.88rem;font-weight:600;line-height:1.3;margin-bottom:3px;}
    .mobile-clinic-name{font-size:.78rem;color:#6c757d;display:block;margin-bottom:6px;}
    .mobile-commercial-footer{display:flex;justify-content:space-between;align-items:center;padding-top:6px;border-top:1px dashed #e0e0e0;}
}
</style>

{{-- ДЕСКТОП --}}
<div class="container desktop-popular-wrapper my-4">
    <div class="row">
        <h2 class="header_h2 mb-4">Выгодные предложения в г. {{ $currentCityName }}</h2>
        <div class="row g-3">
            @foreach($columns as $colIndex => $colPromos)
            <div class="col-4">
                <div class="most_popular_in_city border commercial-card-desktop">
                    <figcaption class="title_list_popular d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $colTitles[$colIndex] ?? 'Акции' }}</span>
                        <span class="badge-discount">% Акции</span>
                    </figcaption>
                    <ul class="list_doctor mb-0">
                        @foreach($colPromos as $promo)
                        @php
                            $entity = $promo->promotable;
                            $typeStr = class_basename($promo->promotable_type);
                            $url = isset($routeMap[$typeStr]) ? route($routeMap[$typeStr], $entity->slug ?? '#') : '#';
                        @endphp
                        <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <a class="link_in_category_specialist font-weight-bold" href="{{ $url }}" title="{{ $promo->title }}">
                                {{ $promo->title }}
                            </a>
                            <small class="text-muted">{{ $entity->name ?? '' }}</small>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    @if($promo->old_price)
                                        <span class="commercial-old-price">{{ number_format($promo->old_price, 0, '.', ' ') }} ₽</span>
                                    @endif
                                    @if($promo->new_price)
                                        <span class="commercial-new-price">{{ number_format($promo->new_price, 0, '.', ' ') }} ₽</span>
                                    @endif
                                </div>
                                <a href="{{ $url }}" class="btn btn-sm btn-outline-primary btn-commercial">Подробнее</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- МОБИЛЕ --}}
<div class="mobile-popular-wrapper">
    <h2 class="mobile-popular-title">Акции в г. {{ $currentCityName }}</h2>
    <div class="mobile-popular-scroll">
        @foreach($columns as $colIndex => $colPromos)
        <div class="mobile-popular-card">
            <div class="mobile-popular-card-header">
                <span>{{ $colTitles[$colIndex] ?? 'Акции' }}</span>
                <span class="badge-discount">%</span>
            </div>
            @foreach($colPromos as $promo)
            @php
                $entity = $promo->promotable;
                $typeStr = class_basename($promo->promotable_type);
                $url = isset($routeMap[$typeStr]) ? route($routeMap[$typeStr], $entity->slug ?? '#') : '#';
            @endphp
            <div class="mobile-popular-item">
                <a class="mobile-popular-link" href="{{ $url }}">{{ $promo->title }}</a>
                <span class="mobile-clinic-name">{{ $entity->name ?? '' }}</span>
                <div class="mobile-commercial-footer">
                    <div>
                        @if($promo->old_price)
                            <span class="commercial-old-price">{{ number_format($promo->old_price, 0, '.', ' ') }} ₽</span>
                        @endif
                        @if($promo->new_price)
                            <span class="commercial-new-price">{{ number_format($promo->new_price, 0, '.', ' ') }} ₽</span>
                        @endif
                    </div>
                    <a href="{{ $url }}" class="btn btn-sm btn-primary btn-commercial">Смотреть</a>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endif
