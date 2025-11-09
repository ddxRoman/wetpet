@if ($record && $record->review)
    <div class="p-4 border rounded-xl bg-gray-50">
        <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        👤 {{ $record->review->user->name ?? 'Неизвестный пользователь' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ $record->review->review_date->format('d.m.Y') }}
                    </p>
                </div>
                <div class="text-yellow-500 font-medium">
                    ⭐ {{ $record->review->rating ?? '—' }}/5
                </div>
            </div>

            <div class="text-gray-700 mt-2">
                <strong>Отзыв:</strong>
                <p class="mt-1 text-sm leading-relaxed">
                    {{ $record->review->content ?? 'Текст отсутствует' }}
                </p>
            </div>


            @if($record->review->liked)
                <p class="text-sm text-green-600 mt-2">
                    👍 {{ $record->review->liked }}
                </p>
            @endif

            @if($record->review->disliked)
                <p class="text-sm text-red-600 mt-1">
                    👎 {{ $record->review->disliked }}
                </p>
            @endif
        </div>
    </div>
@else
    <p class="text-gray-500 italic">Отзыв не найден.</p>
@endif

<div
    x-data="{ open: false, imageUrl: '' }"
    x-on:open-modal.window="if ($event.detail.id === 'receipt-viewer') { imageUrl = $event.detail.url; open = true }"
    x-show="open"
    style="display: none"
    class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center"
>
    <div class="relative max-w-5xl w-full">
        <button
            @click="open = false"
            class="absolute top-4 right-4 text-white text-2xl"
        >
            ✕
        </button>

        <img
            :src="imageUrl"
            alt="Чек"
            class="max-h-[90vh] mx-auto rounded-lg shadow-lg"
        >
    </div>
</div>
