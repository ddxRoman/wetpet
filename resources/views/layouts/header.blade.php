@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
@include('modal.select_city')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <title>Зверополис</title>
</head>
<body class="body_page">
    <header>

        <div class="container">
            <div class="row">
                <div class="col-3">
                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#selectcityModal">Ваш Город</button>
                </div>
                <div class="col-6 header_center_block">            <img class="header_logo" src="{{ Storage::url('logo.png') }}" alt="Зверополис"></div>
                <div class="col-3 profile_block"><a href="" class="login_link">
                    <a href="{{route('login_page')}}">
                        <button type="button" class="btn_login">Войти</button>
                    </a>
                </a>
            </div>
                <div class="row">
                    <div class="col-12">
                        <input type="search" class="header-search">
                       <a href=""><img class="btn_search" src="{{storage::url('icon/search.png')}}" alt=""></a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>

    </main>
    <footer>

    </footer>

    </body>
    </html>



