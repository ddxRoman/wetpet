{{-- Кнопки «Редактировать» / «Удалить» для автора отзыва. Логика — resources/js/review-manage.js --}}
@php
    $reviewPayload = [
        'id'       => $review->id,
        'rating'   => (int) $review->rating,
        'liked'    => $review->liked,
        'disliked' => $review->disliked,
        'content'  => $review->content,
        'photos'   => $review->photos->map(fn ($p) => [
            'id'  => $p->id,
            'url' => asset('storage/' . $p->photo_path),
        ])->values(),
        'receipts' => $review->receipts->map(fn ($r) => [
            'id'   => $r->id,
            'name' => basename($r->path),
        ])->values(),
    ];
@endphp
<div class="mt-1">
    <button type="button" class="btn btn-sm btn-outline-primary"
            data-review-edit
            data-review="{{ json_encode($reviewPayload, JSON_UNESCAPED_UNICODE) }}">
        ✏️ Редактировать
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger"
            data-review-delete="{{ $review->id }}">
        Удалить
    </button>
</div>
