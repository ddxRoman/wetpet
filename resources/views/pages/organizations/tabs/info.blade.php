<style>
.org-info-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
}

.org-info-table td:first-child {
    font-weight: 600;
    color: #333;
    width: 125px;
    vertical-align: top;
}

.org-info-table td {
    padding: 4px 0;
    font-size: 0.95rem;
}

.org-info-table a {
    color: #0d6efd;
    font-weight: 500;
}

.org-info-table img.go-icon {
    width: 16px;
    height: 16px;
    margin-left: 4px;
    transition: 0.2s;
    opacity: 0;
}

.org-info-table a:hover img.go-icon {
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

<table class="org-info-table">
    <tbody>
        {{-- 1. Вывод Адреса (если заполнены street или house) --}}
        @if(!empty($organization->street) || !empty($organization->house))
        <tr>
            <td>Адрес:</td>
            <td>
                {{ $organization->street }}{{ !empty($organization->house) ? ', д.' . $organization->house : '' }}
            </td>
        </tr>
        @endif

        <tr>
            <td>Город:</td>
            <td>
                @if($organization->city)
                    {{ $organization->city }}
                @else
                    —
                @endif
            </td>
        </tr>

        <tr>
            <td>Сфера деятельности:</td>
            <td>
                @if($organization->activityType)
                    {{ $organization->activityType->name }}
                @else
                    —
                @endif
            </td>
        </tr>

        @if(!empty($organization->schedule) || !empty($organization->workdays))
        <tr>
            <td>График работы:</td>
            <td>
                {{ $organization->workdays }}{{ !empty($organization->workdays) && !empty($organization->schedule) ? ', ' : '' }}{{ $organization->schedule }}
            </td>
        </tr>
        @endif

        {{-- 4. Описание --}}
        @if(!empty($organization->description))
        <tr>
            <td colspan="2" class="pt-3">
                <div class="text-muted small mb-1">Об организации:</div>
                {{ $organization->description }}
            </td>
        </tr>
        @endif
    </tbody>
</table>