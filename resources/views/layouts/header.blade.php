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
                <button type="button" class="btn btn_login" data-bs-toggle="modal" data-bs-target="#selectcityModal">Ваш Город</button>
                </div>
                <div class="col-6 header_center_block">            <img class="header_logo" src="{{ Storage::url('logo.png') }}" alt="Зверополис"></div>
                <div class="col-3 profile_block"><a href="" class="login_link">
                    <a href="{{route('login_page')}}">
                        <button type="button" class="btn_login">Войти</button>
                    </a>
                </a>
            </div>
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
    <footer>

    </footer>

    </body>
    </html>



