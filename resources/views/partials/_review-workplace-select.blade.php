{{--
    Необязательный выбор места приёма в форме отзыва о враче/специалисте.
    Показывается, только если человек работает в нескольких местах (клиники и/или организации).
    Пусто — отзыв виден только в карточке врача/специалиста; выбрано место — ещё и в карточке этого места.

    Ожидает: $employee (Doctor | Specialist)
--}}
@php
    $reviewWorkplaces = $employee->allWorkplaces();
@endphp

@if($reviewWorkplaces->count() > 1)
    <div class="mb-3">
        <label class="form-label">Где был приём? <span class="text-muted small">(необязательно)</span></label>
        <select name="workplace" class="form-select">
            <option value="">Не указывать</option>
            @foreach($reviewWorkplaces as $place)
                <option value="{{ $place['key'] }}" @selected(old('workplace') === $place['key'])>
                    {{ $place['name'] }}{{ $place['city'] ? ' — ' . $place['city'] : '' }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Если выбрать место, отзыв появится и в его карточке. Иначе он будет только у специалиста.</div>
    </div>
@endif

