{{-- Переключатель «Другие населённые пункты». Выбор запоминается (сессия + cookie).
     $otherOnly — включён ли фильтр, $baseUrl — URL каталога, $query — доп. параметры (город, тип) --}}
@php
    $query = $query ?? [];
    $toggleUrl = $baseUrl . '?' . http_build_query($query + ['other_localities' => $otherOnly ? 0 : 1]);
@endphp
<style>
    .ol-switch { display: inline-flex; align-items: center; gap: 10px; padding: 6px 14px 6px 8px;
        border: 1px solid #d5dbe1; border-radius: 999px; background: #fff; color: #333;
        font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; transition: border-color .15s; }
    .ol-switch:hover { border-color: #6f42c1; color: #333; }
    .ol-switch__track { position: relative; width: 38px; height: 22px; flex: 0 0 38px; border-radius: 999px;
        background: #ced4da; transition: background .2s; }
    .ol-switch__track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px;
        border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3); transition: transform .2s; }
    .ol-switch--on { border-color: #6f42c1; }
    .ol-switch--on .ol-switch__track { background: #6f42c1; }
    .ol-switch--on .ol-switch__track::after { transform: translateX(16px); }
</style>
<div class="d-flex justify-content-center mb-3">
    <a href="{{ $toggleUrl }}" role="switch" aria-checked="{{ $otherOnly ? 'true' : 'false' }}"
       class="ol-switch {{ $otherOnly ? 'ol-switch--on' : '' }}"
       title="Хутора, посёлки и села региона, которых нет в списке городов. Выбор запоминается.">
        <span class="ol-switch__track"></span>
        <span>Другие населенные пункты региона</span>
    </a>
</div>
