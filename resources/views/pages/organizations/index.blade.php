@extends('layouts.catalog')

@section('content')
        <style>
            /* Стили для горизонтального скролла тегов */
            .specialization-filter-wrapper {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 5px;
            }
            .specialization-filter-wrapper::-webkit-scrollbar {
                height: 4px;
            }
            .specialization-filter-wrapper::-webkit-scrollbar-thumb {
                background: #dee2e6;
                border-radius: 10px;
            }
            .btn-outline-secondary {
                background-color: #f8f9fa;
                border-color: #dee2e6;
                color: #333;
            }
            .btn-outline-secondary:hover {
                background-color: #e9ecef;
                color: #000;
                border-color: #adb5bd;
            }
        </style>

<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог зооорганизаций
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6"> {{ $selectedCity }}</small>
        @endif
    </h1>

    @if(empty($selectedCity))
        <div class="alert alert-info text-center">
            Пожалуйста, выберите город — список организаций будет отображён только для выбранного города.
        </div>
    @else

{{-- БЛОК ТЕГОВ (ФИЛЬТР ПО ТИПУ ЧЕРЕЗ ID) --}}
<div class="specialization-filter-wrapper mb-4">
    <div class="d-inline-flex gap-2 specialization-filter pb-2">
        {{-- Ссылка "Все" --}}
        <a href="{{ route('organizations.index', ['city_id' => $currentCityId]) }}" 
           class="btn btn-sm rounded-pill px-3 {{ empty($selectedTypeId) ? 'btn-primary' : 'btn-outline-secondary' }}">
            Все организации
        </a>

        @foreach($organizationTypes as $type)
            {{-- Ссылка на конкретный ID --}}
            <a href="{{ route('organizations.index', ['type_id' => $type->id, 'city_id' => $currentCityId]) }}" 
               class="btn btn-sm rounded-pill px-3 {{ $selectedTypeId == $type->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $type->name }}
            </a>
        @endforeach
    </div>
</div>

        @if($organizations->isEmpty())
            <div class="alert alert-warning text-center">
                Организации в городе <strong>{{ $selectedCity }}</strong> не найдены. <br>
                <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                    Добавить организацию
                </button>
            </div>
        @else

        <div class="organizations-list">
            <div class="row g-4">
                @foreach ($organizations as $org)
                    <div class="col-lg-3 col-md-4 col-12">
                        <a href="{{ route('organizations.show', $org->slug) }}" class="text-decoration-none text-reset">
                            <div class="card h-100 shadow-sm hover-shadow position-relative transition">
                                
                                @php
                                    $logo = !empty($org->logo) ? asset('storage/' . $org->logo) : asset('storage/organizations/default-org.webp');
                                @endphp

                                <img src="{{ $logo }}" class="card-img-top object-fit-contain p-3" style="height: 200px;" alt="{{ $org->name }}">

                                <div class="card-body">
                                    <h5 class="card-title">{{ $org->name }}</h5>
                                    <p class="card-text mb-1 small">
                                        <i class="bi bi-geo-alt"></i> {{ $org->street }}, {{ $org->house }}
                                    </p>
                                    @if($org->schedule)
                                        <p class="card-text mb-0 small text-muted">
                                            <i class="bi bi-clock"></i> {{ $org->workdays }}: {{ $org->schedule }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        @if($organizations->hasMorePages())
            <div class="text-center mt-5 mb-5">
                <button id="load-more" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" data-url="{{ $organizations->nextPageUrl() }}">
                    Показать еще
                </button>
            </div>
        @endif

        @endif 
    @endif
</div>
@endsection