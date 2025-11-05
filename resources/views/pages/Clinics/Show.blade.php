@extends('layouts.app')

@section('title', $clinic->name)

@section('content')
<div class="d-flex flex-column min-vh-100 bg-white">

    @include('layouts.header')

    <main class="flex-grow-1 container py-5">
        <div class="row">
            <div class="col-lg-8 col-12">

                {{-- Логотип и название --}}
                @php
                    $logo = !empty($clinic->logo)
                        ? asset('storage/' . $clinic->logo)
                        : asset('storage/clinics/logo/default.webp');
                @endphp

                <div class="d-flex align-items-center mb-4 flex-wrap">
                    <img src="{{ $logo }}" alt="{{ $clinic->name }}" class="logo_clinic_card me-3 mb-3 mb-md-0">
                    <h1 class="text-2xl fw-bold m-0">{{ $clinic->name }}</h1>
                </div>

                {{-- Вкладки --}}
                <ul class="nav nav-tabs mb-4" id="clinicTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab">Контакты</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">Услуги</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="directions-tab" data-bs-toggle="tab" data-bs-target="#directions" type="button" role="tab">Направления</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="photos-tab" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">Фото</button>
                    </li>
                </ul>

                <div class="tab-content" id="clinicTabsContent">
                    {{-- Контакты --}}
                    <div class="tab-pane fade show active" id="contacts" role="tabpanel">
                        <div class="text-secondary mb-4">
                            <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
                            <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
                            <div>📞 {{ $clinic->phone1 }} @if($clinic->phone2), {{ $clinic->phone2 }}@endif</div>
                            <div>✉️ {{ $clinic->email }}</div>
                            @if($clinic->telegram)
                                <div>💬 Telegram: {{ $clinic->telegram }}</div>
                            @endif
                            @if($clinic->whatsapp)
                                <div>💬 WhatsApp: {{ $clinic->whatsapp }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Услуги --}}
                    <div class="tab-pane fade" id="services" role="tabpanel">
                        @if(!empty($clinic->services))
                            <ul class="mb-0">
                                @foreach($clinic->services as $service)
                                    <li>{{ $service }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Информация об услугах отсутствует.</p>
                        @endif
                    </div>

                    {{-- Направления --}}
                    <div class="tab-pane fade" id="directions" role="tabpanel">
                        <p>{{ $clinic->directions ?? 'Информация о направлениях отсутствует.' }}</p>
                    </div>

                    {{-- Фото --}}
                    <div class="tab-pane fade" id="photos" role="tabpanel">
                        <div class="row g-3">
                            @if(!empty($clinic->photos))
                                @foreach($clinic->photos as $photo)
                                    <div class="col-md-4 col-sm-6">
                                        <img src="{{ asset('storage/' . $photo) }}" class="img-fluid rounded shadow-sm" alt="Фото клиники">
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Фотографии пока не добавлены.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Доктора --}}
                <div class="mb-4 mt-5">
                    <h2 class="fs-5 fw-semibold mb-3">Доктора</h2>

                    @php
                        $doctors = \App\Models\Doctor::where('clinic', $clinic->name)->get();
                    @endphp

<div class="row g-3">
<div class="mb-4">
    <h2 class="fs-5 fw-semibold mb-3">Доктора</h2>

    <div class="row g-3">
        @forelse ($doctors as $doctor)
            <div class="col-md-6 col-lg-4 col-sm-6">
                <div class="card h-100 shadow-sm border-0 position-relative doctor-card">
                    {{-- Иконка лапки с рейтингом --}}
                    <div class="rating-badge"><img width="24px" src="{{ asset('storage/icon/stars/doctors_stars.png') }}" alt="Рейтинг доктора">

                        <!-- 🐾 -->
                         <span class="rating-value">4.3</span>
                    </div>

                    <div class="card-body text-center">
                        <img src="{{ $doctor->photo ? asset('/' . $doctor->photo) : asset('storage/doctors/default.webp') }}"
                             alt="{{ $doctor->name }}"
                             class="doctor-photo mb-3">
                        <h5 class="card-title mb-1">{{ $doctor->name }}</h5>
                        <p class="text-muted mb-2">{{ $doctor->specialization ?? 'Ветеринар' }}</p>
                        @if($doctor->experience)
                            <p class="small text-secondary">Опыт: {{ $doctor->experience }} лет</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Доктора не указаны.</p>
        @endforelse
    </div>
</div>


                </div>

            </div>

            {{-- Правая колонка --}}
            <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                <div class="card shadow-sm border-0 p-3">
                    <h5 class="fw-semibold mb-2">Карта / Доп. информация</h5>
                    <div class="bg-light p-3 rounded text-center text-muted" style="min-height: 250px;">
                        Здесь можно вставить карту, фото или рекламу клиники.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-fullwidth mt-auto w-100">
        @include('layouts.footer')
    </footer>
</div>

<style>
.logo_clinic_card {
    width: 96px;
    height: 96px;
    object-fit: contain;
    border-radius: 8px;
    background-color: #f8f9fa;
    padding: 6px;
    border: 1px solid #eee;
}

.doctor-photo {
    width: 130px;          /* шире, чем обычное фото */
    height: 180px;         /* вертикально вытянуто */
    object-fit: cover;     /* заполняет область */
    border-radius: 8px;    /* лёгкие скругления */
    border: 1px solid #ddd;
    background-color: #f8f9fa;
}

.nav-tabs .nav-link {
    color: #555;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #000;
    border-color: #dee2e6 #dee2e6 #fff;
}
.footer-fullwidth {
    background-color: #f8f9fa;
    border-top: 1px solid #e5e5e5;
}


.doctor-card {
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.doctor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

/* Фото доктора — вертикальное */
.doctor-photo {
    width: 130px;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
}

/* Лапка с рейтингом — теперь в левом верхнем углу */
.rating-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #fff;
    border-radius: 20px;
    padding: 4px 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #ff7b00;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 4px;
}

.rating-badge .rating-value {
    font-size: 0.9rem;
}
</style>





@endsection
