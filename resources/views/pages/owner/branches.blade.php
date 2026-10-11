@extends('layouts.app')

<title>Работа с филиалами — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-4" style="max-width: 1100px;">

    <div class="mb-3">
        <a href="{{ route('owner.index') }}" class="text-decoration-none text-muted" style="font-size:14px;">← Вернуться в кабинет</a>
    </div>

    <h1 class="fs-3 fw-bold mb-1">Работа с филиалами</h1>
    <div class="text-muted mb-4">{{ $chain->name }}</div>

    @if(session('branch_success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            ✅ {{ session('branch_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Переключатель сетей, если пользователь состоит в нескольких --}}
    @if($chains->count() > 1)
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($chains as $c)
                <a href="{{ route('owner.branches', $c->id) }}"
                   class="btn btn-sm {{ $c->id === $chain->id ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">{{ $c->name }}</a>
            @endforeach
        </div>
    @endif

    {{-- ─────────── Список филиалов ─────────── --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <h2 class="fs-5 fw-semibold mb-3">Филиалы сети <span class="text-muted fw-normal">({{ $branches->count() }})</span></h2>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:14px;">
                    <thead>
                        <tr class="text-muted">
                            <th style="width:56px;">Лого</th>
                            <th>Филиал</th>
                            <th>График</th>
                            <th>Телефон</th>
                            <th>Почта</th>
                            <th>Сайт</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $b)
                            <tr>
                                <td>
                                    @if($b['logo'])
                                        <img src="{{ Storage::url($b['logo']) }}" alt="" class="rounded-3"
                                             style="width:44px;height:44px;object-fit:cover;">
                                    @else
                                        <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                             style="width:44px;height:44px;">{{ $b['type'] === 'clinic' ? '🏥' : '🏢' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        @if($b['owned'])
                                            <a href="{{ $b['url'] }}" class="text-decoration-none">{{ $b['name'] }}</a>
                                        @else
                                            {{ $b['name'] }}
                                        @endif
                                    </div>
                                    <div class="text-muted">
                                        {{ $b['city'] }}{{ $b['address'] !== '' ? ', ' . $b['address'] : '' }}
                                        · {{ $b['label'] }}
                                    </div>
                                    @unless($b['owned'])
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">Только просмотр — вы не владелец этого филиала</span>
                                    @endunless
                                </td>
                                <td>
                                    {{ $b['workdays'] ?: '—' }}<br>
                                    <span class="text-muted">{{ $b['schedule'] ?: '' }}</span>
                                </td>
                                <td>
                                    {{ $b['phone1'] ?: '—' }}
                                    @if($b['phone2'])<br><span class="text-muted">{{ $b['phone2'] }}</span>@endif
                                </td>
                                <td>{{ $b['email'] ?: '—' }}</td>
                                <td>{{ $b['website'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($manageable->isEmpty())
        <div class="alert alert-info rounded-3">У вас нет подтверждённых филиалов в этой сети, поэтому изменять данные нельзя.</div>
    @else
        <p class="text-muted" style="font-size:14px;">
            В каждом блоке ниже выберите, куда применить настройку: ко всем вашим филиалам или только к выбранным.
            Пустые поля не применяются: у филиалов остаётся прежнее значение.
        </p>

        @php
            $blocks = [
                'logo'     => ['icon' => '🖼️', 'title' => 'Общий логотип'],
                'schedule' => ['icon' => '🕒', 'title' => 'График работы'],
                'phone'    => ['icon' => '📞', 'title' => 'Телефон'],
                'email'    => ['icon' => '✉️', 'title' => 'Почта'],
                'website'  => ['icon' => '🌐', 'title' => 'Сайт'],
            ];
        @endphp

        <div class="row g-4">
            @foreach($blocks as $group => $block)
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h2 class="fs-5 fw-semibold mb-3">{{ $block['icon'] }} {{ $block['title'] }}</h2>

                            @if(session('branch_error') && session('branch_error_group') === $group)
                                <div class="alert alert-danger py-2">{{ session('branch_error') }}</div>
                            @endif

                            <form method="POST" action="{{ route('owner.branches.apply', $chain->id) }}"
                                  enctype="multipart/form-data" data-branch-form>
                                @csrf
                                <input type="hidden" name="group" value="{{ $group }}">

                                @if($group === 'logo')
                                    <div class="mb-3">
                                        <label class="form-label">Файл логотипа</label>
                                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                                        <div class="form-text">JPG, PNG или WEBP, до 2 МБ. Каждому филиалу сохранится собственная копия.</div>
                                    </div>
                                @elseif($group === 'schedule')
                                    <div class="row g-2 mb-3">
                                        <div class="col-sm-6">
                                            <label class="form-label">Часы работы</label>
                                            <input type="text" name="schedule" class="form-control" placeholder="09:00–20:00" maxlength="255">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Рабочие дни</label>
                                            <input type="text" name="workdays" class="form-control" placeholder="Пн–Вс" maxlength="255">
                                        </div>
                                    </div>
                                @elseif($group === 'phone')
                                    <div class="row g-2 mb-3">
                                        <div class="col-sm-6">
                                            <label class="form-label">Основной телефон</label>
                                            <input type="text" name="phone1" class="form-control" maxlength="30">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Дополнительный телефон</label>
                                            <input type="text" name="phone2" class="form-control" maxlength="30">
                                        </div>
                                    </div>
                                @elseif($group === 'email')
                                    <div class="mb-3">
                                        <label class="form-label">Почта</label>
                                        <input type="email" name="email" class="form-control" maxlength="255" required>
                                    </div>
                                @elseif($group === 'website')
                                    <div class="mb-3">
                                        <label class="form-label">Сайт</label>
                                        <input type="url" name="website" class="form-control" placeholder="https://example.com" maxlength="255" required>
                                    </div>
                                @endif

                                @include('pages.owner._branch-target', ['group' => $group, 'manageable' => $manageable])

                                <button type="submit" class="btn btn-primary rounded-pill px-4">Применить</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    // Показываем список филиалов только при выборе «Выбрать филиалы»
    document.querySelectorAll('[data-branch-target]').forEach(function (box) {
        var list = box.querySelector('[data-branch-list]');
        box.querySelectorAll('input[name="target"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                list.classList.toggle('d-none', radio.value !== 'selected' || !radio.checked);
            });
        });
    });

    // Не даём отправить «Выбрать филиалы» без единой отметки
    document.querySelectorAll('[data-branch-form]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var selected = form.querySelector('input[name="target"][value="selected"]');
            if (selected && selected.checked && !form.querySelector('input[name="branch_ids[]"]:checked')) {
                e.preventDefault();
                alert('Отметьте хотя бы один филиал.');
            }
        });
    });
</script>

@include('layouts.footer')
@endsection
