{{--
    Единая вкладка «Информация» для организации и клиники: вид деятельности
    и вся контактная информация в одном месте.

    Ожидает:
    $entity — Organization|Clinic
--}}
@php
    $isClinic = $entity instanceof \App\Models\Clinic;
@endphp

<style>
.doctor-info-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
}
.doctor-info-table td:first-child {
    font-weight: 600;
    color: #333;
    width: 160px;
    vertical-align: top;
    white-space: nowrap;
}
.doctor-info-table td {
    padding: 4px 0;
    font-size: 0.95rem;
}
.doctor-info-table a {
    color: #0d6efd;
    font-weight: 500;
    text-decoration: none;
}
.doctor-info-table a:hover {
    text-decoration: underline;
}
</style>

<table class="doctor-info-table">
    <tbody>
        {{-- Вид деятельности --}}
        <tr>
            <td>Вид деятельности:</td>
            <td>
                @if($isClinic)
                    Ветеринарная клиника
                @else
                    {{ $entity->activityType->name ?? '—' }}
                @endif
            </td>
        </tr>

        {{-- Адрес --}}
        <tr>
            <td>Адрес:</td>
            <td>
                {{ implode(', ', array_filter([
                    $entity->country ?? null,
                    $entity->region ?? null,
                    $entity->city ?? null,
                    $entity->street ?? null,
                    $entity->house ?? null,
                ])) ?: '—' }}
            </td>
        </tr>

        {{-- Режим работы --}}
        @if(!empty($entity->workdays) || !empty($entity->schedule))
        <tr>
            <td>Режим работы:</td>
            <td>{{ $entity->workdays ?? 'Дни не указаны' }} — {{ $entity->schedule ?? 'Время не указано' }}</td>
        </tr>
        @endif

        {{-- Телефоны --}}
        @if(!empty($entity->phone1))
        <tr>
            <td>Телефон:</td>
            <td>
                <a href="tel:{{ preg_replace('/\D/', '', $entity->phone1) }}">{{ $entity->phone1 }}</a>
                @if(!empty($entity->phone2))
                    , <a href="tel:{{ preg_replace('/\D/', '', $entity->phone2) }}">{{ $entity->phone2 }}</a>
                @endif
            </td>
        </tr>
        @endif

        {{-- Почта --}}
        @if(!empty($entity->email))
        <tr>
            <td>Почта:</td>
            <td><a href="mailto:{{ $entity->email }}">{{ $entity->email }}</a></td>
        </tr>
        @endif

        {{-- Telegram --}}
        @if(!empty($entity->telegram))
        <tr>
            <td>Telegram:</td>
            <td>
                <a href="https://t.me/{{ $entity->telegram }}" target="_blank" rel="noopener">
                    https://t.me/{{ $entity->telegram }}
                </a>
            </td>
        </tr>
        @endif

        {{-- VK --}}
        @if(!empty($entity->whatsapp))
        <tr>
            <td>VK:</td>
            <td><a href="{{ $entity->whatsapp }}" target="_blank" rel="noopener">{{ $entity->whatsapp }}</a></td>
        </tr>
        @endif

        {{-- Сайт --}}
        @if(!empty($entity->website))
        <tr>
            <td>Сайт:</td>
            <td><a href="{{ $entity->website }}" target="_blank" rel="noopener">Перейти на сайт 🌐</a></td>
        </tr>
        @endif

        {{-- Описание --}}
        @if(!empty($entity->description))
        <tr>
            <td colspan="2" class="pt-3">
                <div class="text-muted small mb-1">{{ $isClinic ? 'О клинике' : 'Об организации' }}:</div>
                {{ $entity->description }}
            </td>
        </tr>
        @endif
    </tbody>
</table>
