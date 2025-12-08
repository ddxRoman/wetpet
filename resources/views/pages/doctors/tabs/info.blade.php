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
    opacity: 0.8;
    transition: 0.2s;
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
        <tr>
            <td>Клиника:</td>
            <td>
                @if($doctor->clinic)
                    <a href="{{ route('clinics.show', $doctor->clinic->id) }}" class="text-decoration-none">
                        {{ $doctor->clinic->name }}
                        <img src="{{ asset('storage/icon/button/gosite.svg') }}" class="go-icon" alt="">
                    </a>
                @else
                    —
                @endif
            </td>
        </tr>

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

        
                <tr>
                    <td>Образование:</td>
                    <td style="color: red;"> Надо сделать проверку, и выводить образование если указано</td>
                </tr>
                        @if($doctor->exotic_animals == 'Да')
        <tr>
             <td colspan="2">
                <span class="exotic-badge">Специалист по экзотическим животным</span>
            </td>
        </tr>
        @endif
                <tr>
                    <td colspan="2"> {{ $doctor->description }}</td>
                </tr>




    </tbody>
</table>
