@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <label for="name">Имя</label>
            <input id="name" type="text" name="name" required autofocus>
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
        </div>
        <div>
            <label for="phone">Телефон</label>
            <input id="phone" type="phone" name="phone" required>
        </div>
        <div>
            <label for="password">Пароль</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div>
            <label for="password-confirm">Подтвердите пароль</label>
            <input id="password-confirm" type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Зарегистрироваться</button>
    </form>
@endsection