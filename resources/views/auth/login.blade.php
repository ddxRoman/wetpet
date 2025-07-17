@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

    <form method="POST" action="{{ route('login') }}">
        @csrf
<div>
    <label for="login">Email или телефон</label>
    <input id="login" type="text" name="login" required autofocus>
</div>
        <div>
            <label for="password">Пароль</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div>
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Запомнить меня</label>
        </div>
        <button type="submit">Войти</button>
    </form>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://snipp.ru/cdn/maskedinput/jquery.maskedinput.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    <script src="https://snipp.ru/cdn/maskedinput/jquery.maskedinput.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
</head>
@section('content')
<body class="body_page">
    <div class="container login-page">
    <div class="row">
        <div class="col-12 logo-block_login-page">
            <img class="page-login_logo" src="{{ Storage::url('logo.png') }}" alt="Зверополис">
        </div>
    </div>    
        <div class="row">
            <div class="col-12 login-block">
                    <form method="POST" action="{{ route('login') }}">
                    @CSRF

    <input id="login" type="text" name="login" required autofocus>

                    <input class="login_input" type="password" id="password" placeholder="Пароль" required><br> 
                    <div>
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Запомнить меня</label>
    </div>
                    <button class="login_btn_page">Войти</button>
                </form>
                <hr class="hr_login_page">
                <p>
                   <h3>
                       или авторризуйтесь через:
                   </h3> 
                </p>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/mailru.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/telegram.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/vk.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/yandex.svg') }}" alt="">
                </a>
                
            </div>
        </div>
    </div>
</body>
@endsection
</html>

	<script>
                                $('.mask-phone').mask('+7 (999) 999-99-99');
                            </script>
