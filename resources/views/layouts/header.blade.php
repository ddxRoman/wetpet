@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
@include('modal.select_city')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <title>Мяу-Гав</title>
</head>


@include('partials.city-selector')


{{-- Для гостей (неавторизованных) --}}

<body class="body_page">
    <header>
        <div class="container">
            <div class="row">
                <div class="col-3">
                <button type="button" class="btn_city" data-bs-toggle="modal" data-bs-target="#selectcityModal">Ваш Город</button>
                </div>
                <div class="col-6 header_center_block">     <a href="/">     <img class="header_logo" src="{{ Storage::url('logo3.png') }}" alt="Зверополис"> </a>  </div>
                @guest
                <div class="col-3 profile_block">
                    <a class="login_link" href="{{route('login')}}">
                        <button type="button" class="btn_login">Войти</button>
                        
                    </a>
</div> 
@endguest
@auth
<!-- Authentication Links -->
<div class=" col-3 profile_block">
    <a id="navbarDropdown" class="profile_link " href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
    @php
        $randomNumber = rand(0, 20);
        $link="storage/avatars/default/$randomNumber.png"

    @endphp
<br>

<img class="avatars_pics" src="{{asset("{$link}")}}">
    {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <ul class="dropdown_menu_li">
<li>

    <a class="dropdown-profile" href="{{ route('logout') }}">
        Профиль
    </a>
</li>
<li>

    <a class="dropdown-profile" href="{{ route('logout') }}"
    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                </li>
                                </ul>
                                </div>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                         </div>
                            @endauth
            <div class="description_view col-12">
                <h1>Сайт про домашних животных</h1>
                <div>На сайте вы сможете найти: ветеринарные клиники, ветгостиницы, лекарства, ветеринаров и грумеров <br>
                    а так же прочесть отзывы о породе от владельцев</div>
            </div>
                <div class="row">
                    <div class="col-12">
                        <input type="search" class="header-search" placeholder="Животные, породы, ветеринары, клиники ">
                       <a href=""><img class="btn_search" src="{{storage::url('icon/search.png')}}" alt=""></a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>
    </main>
    </body>
    </html>



