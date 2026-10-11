{{-- Поле «Фотографии» в форме отзыва: несколько фото — только для Премиум, иначе одно --}}
@php
    $__photoMax   = auth()->check() ? auth()->user()->reviewPhotoLimit() : (int) config('premium.limits.review_photos.free', 1);
    $__photoMulti = $__photoMax > 1;
@endphp
<div class="mb-3">
    <label class="form-label">{{ $__photoMulti ? 'Фотографии:' : 'Фотография:' }}</label>
    <input type="file" name="photos[]" @if($__photoMulti) multiple @endif accept="image/*" class="form-control"
           data-max-photos="{{ $__photoMax }}">
    @if($__photoMulti)
        <div class="form-text">До {{ $__photoMax }} фото.</div>
    @else
        <div class="form-text">
            Можно прикрепить одно фото. Несколько фото — с <a href="{{ route('account') }}#premium">Премиум</a>.
        </div>
    @endif
</div>
