@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm p-4 text-center">
        <h2 class="mb-4">Объекты на модерации ⏳</h2>
        <p class="text-muted">Ваши заявки на управление успешно отправлены администраторам «Зверозора». Мы проверяем документы в течение 24 часов.</p>
        
        <div class="list-group my-3 text-start mx-auto" style="max-width: 500px;">
            @foreach($pendingOwners as $item)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="me-2">{{ $item['icon'] }}</span>
                        <strong>{{ $item['entity_name'] }}</strong>
                    </div>
                    <span class="badge bg-warning text-dark">Проверяется</span>
                </div>
            @endforeach
        </div>

        <a href="{{ route('account') }}" class="btn btn-primary mt-3">Вернуться в профиль</a>
    </div>
</div>
@endsection