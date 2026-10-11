@php
    $__typeLabels = [
        'Clinic'       => 'Клиника',
        'Doctor'       => 'Врач',
        'Specialist'   => 'Специалист',
        'Organization' => 'Организация',
    ];
@endphp

@foreach($reviews as $review)
    @php
        $target = $review->reviewable;
        $type   = class_basename($review->reviewable_type);
        $author = $review->user;

        // Если у объекта нет slug/города, маршрут не собрать — выводим название без ссылки
        try {
            $targetUrl = match ($type) {
                'Doctor'       => route('doctors.show', $target->slug),
                'Specialist'   => route('specialists.show', $target->slug),
                'Clinic'       => route('clinics.show', ['city' => $target->city_slug, 'clinic' => $target->slug]),
                'Organization' => route('organizations.show', ['city' => $target->city_slug, 'slug' => $target->slug]),
                default        => null,
            };
        } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
            $targetUrl = null;
        }
    @endphp

    <div class="trusted-review">
        <div class="trusted-review__head">
            <a href="{{ route('user.profile', $author->id) }}" class="trusted-review__author">
                <img src="{{ $author->avatar ? asset('storage/' . $author->avatar) : asset('storage/avatars/default/default_avatar.webp') }}"
                     alt="" width="32" height="32" style="border-radius:50%; object-fit:cover;">
                {{ $author->name }}
            </a>
            <span>{{ optional($review->review_date)->format('d.m.Y') }}</span>
        </div>

        <div>
            <span class="badge bg-light text-dark border">{{ $__typeLabels[$type] ?? $type }}</span>
            @if($targetUrl)
                <a href="{{ $targetUrl }}" style="font-weight:600;">{{ $target->name }}</a>
            @else
                <span style="font-weight:600;">{{ $target->name }}</span>
            @endif
        </div>

        @if($review->rating)
            <div style="margin:6px 0;">
                @for ($i = 1; $i <= 5; $i++)
                    <img src="{{ asset('storage/icon/button/' . ($i <= $review->rating ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                         width="18" alt="звезда">
                @endfor
            </div>
        @endif

        @if($review->liked)
            <div style="white-space:pre-line;"><strong style="color:#198754;">Понравилось:</strong> {{ $review->liked }}</div>
        @endif
        @if($review->disliked)
            <div style="white-space:pre-line;"><strong style="color:#dc3545;">Не понравилось:</strong> {{ $review->disliked }}</div>
        @endif
        @if($review->content)
            <p style="white-space:pre-line; margin:6px 0 0;">{{ $review->content }}</p>
        @endif

        @if($review->pet)
            <div style="font-size:13px; color:#777; margin-top:6px;">
                <em>Питомец:</em> {{ $review->pet->name }}@if($review->pet->animal) ({{ $review->pet->animal->species }}@if($review->pet->animal->breed) — {{ $review->pet->animal->breed }}@endif)@endif
            </div>
        @endif

        @if($review->photos->isNotEmpty())
            <div class="trusted-review__photos">
                @foreach($review->photos as $photo)
                    <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Фото отзыва">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endforeach
