@php
    use App\Filament\Resources\ReviewDisputeResource;
    use App\Models\ReviewDispute;
    use App\Models\ReviewDisputeMessage;

    $dispute = $this->getRecord();
    $review  = $dispute->review;
    $entity  = $review?->reviewable;
    $author  = $review?->user;
    $owner   = $dispute->opener;

    $statusColor = match ($dispute->status) {
        ReviewDispute::STATUS_OPEN     => 'warning',
        ReviewDispute::STATUS_RESTORED => 'success',
        ReviewDispute::STATUS_REMOVED  => 'danger',
        default                        => 'gray',
    };
@endphp

<x-filament-panels::page>

    {{-- ───────── Отзыв ───────── --}}
    <x-filament::section>
        <x-slot name="heading">
            Отзыв
            <x-filament::badge :color="$statusColor" class="ml-2" style="display:inline-flex;">
                {{ $dispute->status_label }}
            </x-filament::badge>
        </x-slot>

        <div class="grid gap-1 text-sm" style="margin-bottom:12px;">
            <div>
                <strong>{{ ReviewDisputeResource::typeLabel($review?->reviewable_type) ?: 'Карточка' }}:</strong>
                {{ $entity?->name ?? 'карточка удалена' }}
            </div>
            <div>
                <strong>Автор отзыва:</strong> {{ $author?->name ?? '—' }}
                @if($author?->email) <span style="color:#6b7280;">({{ $author->email }})</span> @endif
            </div>
            <div>
                <strong>Оспорил владелец:</strong> {{ $owner?->name ?? '—' }}
                @if($owner?->email) <span style="color:#6b7280;">({{ $owner->email }})</span> @endif
                <span style="color:#6b7280;">· {{ $dispute->created_at->format('d.m.Y H:i') }}</span>
            </div>
            @if($dispute->resolved_at)
                <div><strong>Решение принято:</strong> {{ $dispute->resolved_at->format('d.m.Y H:i') }}</div>
            @endif
        </div>

        <div style="background:rgba(127,127,127,.08);border:1px solid rgba(127,127,127,.25);border-radius:10px;padding:14px 16px;">
            <div style="color:#f5a623;letter-spacing:2px;font-size:18px;" title="Оценка {{ (int) $review?->rating }} из 5">
                {{ str_repeat('★', (int) $review?->rating) }}{{ str_repeat('☆', 5 - (int) $review?->rating) }}
            </div>
            <div style="font-size:12px;color:#6b7280;margin:2px 0 8px;">
                {{ $review?->review_date?->format('d.m.Y') }}
                @if($review?->receipt_verified == 1 || $review?->receipt_verified === 'verified') · ✅ подтверждённый визит @endif
            </div>

            @if($review?->liked)
                <div><strong style="color:#16a34a;">Понравилось:</strong> {{ $review->liked }}</div>
            @endif
            @if($review?->disliked)
                <div><strong style="color:#dc2626;">Не понравилось:</strong> {{ $review->disliked }}</div>
            @endif
            @if($review?->content)
                <p style="margin:8px 0 0;white-space:pre-wrap;">{{ $review->content }}</p>
            @endif

            @if($review && $review->photos->count())
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;">
                    @foreach($review->photos as $photo)
                        <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Фото отзыва"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid rgba(127,127,127,.3);">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if($dispute->reason)
            <div style="margin-top:12px;font-size:14px;">
                <strong>Причина жалобы (от владельца):</strong>
                <div style="white-space:pre-wrap;">{{ $dispute->reason }}</div>
            </div>
        @endif
    </x-filament::section>

    {{-- ───────── Два диалога ───────── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;align-items:start;">

        @foreach([
            ['party' => ReviewDisputeMessage::PARTY_OWNER,  'title' => 'Диалог с владельцем карточки', 'who' => $owner,  'form' => 'ownerForm',  'send' => 'sendToOwner'],
            ['party' => ReviewDisputeMessage::PARTY_AUTHOR, 'title' => 'Диалог с автором отзыва',     'who' => $author, 'form' => 'authorForm', 'send' => 'sendToAuthor'],
        ] as $col)
            @php $messages = $this->dialogMessages($col['party']); @endphp

            <x-filament::section>
                <x-slot name="heading">{{ $col['title'] }}</x-slot>
                <x-slot name="description">{{ $col['who']?->name ?? '—' }}</x-slot>

                <div style="display:flex;flex-direction:column;gap:10px;max-height:420px;overflow-y:auto;padding-right:4px;margin-bottom:14px;">
                    @forelse($messages as $m)
                        <div style="max-width:88%;padding:8px 12px;border-radius:12px;font-size:14px;white-space:pre-wrap;word-break:break-word;
                                    {{ $m->is_admin ? 'align-self:flex-end;background:rgba(34,197,94,.15);' : 'align-self:flex-start;background:rgba(99,102,241,.15);' }}">
                            <div style="font-size:11px;color:#6b7280;margin-bottom:2px;">
                                {{ $m->is_admin ? 'Вы (админ)' : ($m->user?->name ?? 'Пользователь') }} · {{ $m->created_at->format('d.m.Y H:i') }}
                            </div>
                            @if($m->message)<div>{{ $m->message }}</div>@endif

                            @if($m->files->count())
                                <div style="margin-top:6px;">
                                    @foreach($m->files as $file)
                                        <a href="{{ $file->url }}" target="_blank" rel="noopener" style="display:inline-block;margin:2px 8px 2px 0;font-size:13px;text-decoration:underline;">
                                            @if($file->is_image)
                                                <img src="{{ $file->url }}" alt="{{ $file->original_name }}" style="max-width:140px;max-height:100px;border-radius:6px;display:block;">
                                            @else
                                                📄 {{ $file->original_name }}
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div style="color:#9ca3af;font-size:14px;">Сообщений пока нет.</div>
                    @endforelse
                </div>

                @if($dispute->isOpen())
                    <form wire:submit="{{ $col['send'] }}">
                        {{ $this->{$col['form']} }}

                        <div style="margin-top:12px;">
                            <x-filament::button type="submit" wire:target="{{ $col['send'] }}">
                                Отправить {{ $col['party'] === ReviewDisputeMessage::PARTY_OWNER ? 'владельцу' : 'автору отзыва' }}
                            </x-filament::button>
                        </div>
                    </form>
                @else
                    <div style="color:#6b7280;font-size:14px;">Спор закрыт — новые сообщения отправить нельзя.</div>
                @endif
            </x-filament::section>
        @endforeach
    </div>

</x-filament-panels::page>
