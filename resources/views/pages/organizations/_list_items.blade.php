@foreach ($organizations as $org)
    <div class="col-lg-3 col-md-4 col-12 organization-item">
        <a href="{{ route('organizations.show', $org->slug) }}" class="text-decoration-none text-reset">
            <div class="card h-100 shadow-sm hover-shadow position-relative transition">
                @php
                    $avgRating = number_format($org->reviews_avg_rating ?? 0, 1);
                    $reviewCount = $org->reviews_count ?? 0;
                @endphp

                <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                     style="z-index: 10;"
                     data-bs-toggle="tooltip"
                     title="Всего отзывов: {{ $reviewCount }}">
                    ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                </div>

                @php
                    $logo = !empty($org->logo) ? asset('storage/' . $org->logo) : asset('storage/organizations/default-org.webp');
                @endphp

                <img src="{{ $logo }}" class="card-img-top object-fit-contain p-3" style="height: 200px;" alt="{{ $org->name }}">

                <div class="card-body">
                    <h5 class="card-title">{{ $org->name }}</h5>
                    @if($org->fieldOfActivity)
                        <p class="card-text mb-1 small text-muted">
                            {{ $org->fieldOfActivity->name }}321321312
                        </p>
                    @endif
                    <p class="card-text mb-1 small">
                        <i class="bi bi-geo-alt"></i> {{ $org->street }}, {{ $org->house }}
                    </p>
                    @if($org->schedule)
                        <p class="card-text mb-0 small text-muted">
                            <i class="bi bi-clock"></i> {{ $org->workdays }}: {{ $org->schedule }}
                        </p>
                    @endif
                    @include('partials._promotions-badge', ['entity' => $org])
                </div>
            </div>
        </a>
    </div>
@endforeach
