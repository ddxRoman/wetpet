{{--
    Пометка над отзывом, оставленным врачу или специалисту, который работает
    (или работал) в этой клинике/организации. Места работы перекрёстные: врач
    может быть и в клинике, и в организации, специалист — тоже, поэтому тут
    обрабатываются оба типа.

    Ожидает: $review
--}}
@php
    $isDoctorReview     = $review->reviewable_type === \App\Models\Doctor::class;
    $isSpecialistReview = $review->reviewable_type === \App\Models\Specialist::class;
@endphp

@if($isDoctorReview || $isSpecialistReview)
    @php $aboutEmployee = $review->reviewable; @endphp
    <div class="small mb-2">
        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
            {{ $isDoctorReview ? 'Отзыв о враче' : 'Отзыв о специалисте' }}
            @if($aboutEmployee)
                <a href="{{ $isDoctorReview
                                ? route('doctors.show', $aboutEmployee)
                                : route('specialists.show', ['slug' => $aboutEmployee->slug]) }}"
                   class="text-decoration-underline text-reset">{{ $aboutEmployee->name }}</a>
            @endif
        </span>
        @if(!$review->specialistStillWorksHere())
            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle ms-1">
                Специалист тут больше не работает
            </span>
        @endif
    </div>
@endif
