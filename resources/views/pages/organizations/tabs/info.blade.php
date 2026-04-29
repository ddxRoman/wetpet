<style>
.doctor-info-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
}

.doctor-info-table td:first-child {
    font-weight: 600;
    color: #333;
    width: 120px;
    vertical-align: top;
}

.doctor-info-table td {
    padding: 4px 0;
    font-size: 0.95rem;
}

.doctor-info-table a {
    color: #0d6efd;
    font-weight: 500;
}

.doctor-info-table img.go-icon {
    width: 16px;
    height: 16px;
    margin-left: 4px;
    transition: 0.2s;
    opacity: 0;
}

.doctor-info-table a:hover img.go-icon {
    opacity: 1;
}

.exotic-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #ffdd57;
    color: #000;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 6px;
}
</style>

<table class="doctor-info-table">
    <tbody>
        {{-- 1. Вывод Адреса (если заполнены street или house) --}}
        @if(!empty($doctor->street) || !empty($doctor->house))
        <tr>
            <td>Адрес:</td>
            <td>
                {{ $doctor->street }}{{ !empty($doctor->house) ? ', д.' . $doctor->house : '' }}
            </td>
        </tr>
        @endif

        @if($doctor->organization)
        <tr>
            <td>Клиника:</td>
            <td>
                <a href="{{ route('organizations.show', $doctor->organization->slug) }}" title="Перейти на страницу клиники" class="text-decoration-none">
                    {{ $doctor->organization->name }}
                    <img src="{{ asset('storage/icon/button/gosite.svg') }}" class="go-icon" alt="Перейти к клинике">
                </a>
            </td>
        </tr>
        @endif

        <tr>
            <td>Город:</td>
            <td>
                @if($doctor->city)
                    {{ $doctor->city->name }}
                @else
                    —
                @endif
            </td>
        </tr>

        <tr>
            <td>Стаж:</td>
            <td>
                @if($doctor->experience)
                    {{ $doctor->experience }} лет
                @else
                    —
                @endif
            </td>
        </tr>

        {{-- 3. Специалист по экзотам --}}
        @if($doctor->exotic_animals == 'Да')
        <tr>
             <td colspan="2">
                <span class="exotic-badge">Специалист по экзотическим животным</span>
            </td>
        </tr>
        @endif

        {{-- 4. Описание --}}
        @if(!empty($doctor->description))
        <tr>
            <td colspan="2" class="pt-3">
                <div class="text-muted small mb-1">О себе:</div>
                {{ $doctor->description }}
            </td>
        </tr>
        @endif
    </tbody>
</table>