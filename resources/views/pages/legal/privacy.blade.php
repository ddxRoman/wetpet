@vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])
<title>Политика конфиденциальности — Зверозор</title>
@extends('layouts.app')

@section('content')
<div class="header_in_account">
    @include('layouts.header')
</div>

<div class="container my-5" style="max-width: 900px;">
    {{-- Заголовок страницы --}}
    <div class="text-center mb-5">
        <h2 class="legal_h2 fw-bold text-dark mb-2">Политика конфиденциальности</h2>
        <p class="text-muted small">Действующая редакция. Территория Российской Федерации</p>
    </div>

    
    
    {{-- Основной блок с контентом --}}
    <div class="card shadow-sm border-0 p-4 p-md-5 bg-white mb-5" style="border-radius: 16px;">
        
        {{-- Вводная плашка --}}
        <div class="p-3 mb-4 bg-light rounded-3 border-start border-primary border-4">
            <p class="text-secondary mb-0 @style('line-height: 1.6; font-size: 0.95rem;')">
                Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных пользователей 
                (далее — «Пользователи») в сети интернет  на сайте <a href="https://zverozor.ru" target="_blank" class="text-decoration-none fw-medium">zverozor.ru</a> (далее — «Сайт»).
            </p>
                        <p class="text-secondary mb-0 @style('line-height: 1.6; font-size: 0.95rem;')">
Оператором персональных данных является: <b>{{ config('company.fio') }}</b> ИНН: <b>{{ config('company.inn') }}</b>, адрес регистрации: <b>{{ config('company.address') }}</b> (далее — «Администрация» или «Сайт»).
        </p>
        </div>

        {{-- Разделы документа --}}
        <div class="legal-content text-secondary" style="font-size: 0.95rem; line-height: 1.7;">
            
            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">1. Общие положения</h5>
                <ul class="list-unstyled ps-0">
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">1.1.</span>
                        <span>Вся информация, собранная с Пользователей, используется в соответствии с Федеральным законом Российской Федерации № 152-ФЗ «О персональных данных».</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">1.2.</span>
                        <span>Используя Сайт, Пользователь соглашается с условиями данной Политики конфиденциальности.</span>
                    </li>
                </ul>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">2. Сбор и использование данных</h5>
                <p class="mb-2 fw-medium text-dark">2.1. Мы собираем следующие данные:</p>
                <ul class="list-unstyled ps-3 mb-3">
                    <li class="mb-2">• <strong class="text-dark">Персональные данные:</strong> имя, электронная почта, телефон, адрес.</li>
                    <li class="mb-2">• <strong class="text-dark">Технические данные:</strong> IP-адрес, данные о браузере, операционной системе, время доступа.</li>
                    <li class="mb-2">• <strong class="text-dark">Финансовые данные:</strong> информация о финансовых операциях, если они связаны с предоставлением наших услуг (например, транзакции с цифровыми финансовыми активами).</li>
                </ul>

                <p class="mb-2 fw-medium text-dark">2.2. Эти данные используются для:</p>
                <ul class="list-unstyled ps-3">
                    <li class="mb-1">— Обработки запросов Пользователей.</li>
                    <li class="mb-1">— Проведения финансовых операций.</li>
                    <li class="mb-1">— Обеспечения безопасности и защиты данных.</li>
                    <li class="mb-1">— Улучшения качества предоставляемых услуг.</li>
                </ul>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">3. Хранение данных</h5>
                <ul class="list-unstyled ps-0">
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">3.1.</span>
                        <span>Все персональные данные Пользователей хранятся на защищенных серверах, доступ к которым ограничен.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">3.2.</span>
                        <span>Мы принимаем все необходимые технические и организационные меры для защиты данных от несанкционированного доступа, потери, уничтожения или изменения.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">3.3.</span>
                        <span>Все персональные данные, которые мы собираем, хранятся на серверах, расположенных на территории Российской Федерации, в соответствии с требованиями Федерального закона «О персональных данных».</span>
                    </li>
                </ul>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">4. Разглашение данных</h5>
                <ul class="list-unstyled ps-0">
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">4.1.</span>
                        <span>Мы не передаем персональные данные третьим лицам, за исключением случаев, предусмотренных законодательством РФ.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">4.2.</span>
                        <span>В случае, если этого требует закон, мы можем передать персональные данные в уполномоченные органы власти.</span>
                    </li>
                </ul>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">5. Права Пользователей</h5>
                <p class="mb-2 fw-medium text-dark">5.1. Пользователь имеет право:</p>
                <ul class="list-unstyled ps-3">
                    <li class="mb-1">— Получить информацию о своих персональных данных, которые мы храним.</li>
                    <li class="mb-1">— Требовать исправления или удаления своих персональных данных.</li>
                    <li class="mb-1">— Отозвать согласие на обработку данных в любой момент, отправив запрос на наш контактный адрес.</li>
                </ul>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <h5 class="text-dark fw-semibold mb-3">6. Заключительные положения</h5>
                <ul class="list-unstyled ps-0">
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">6.1.</span>
                        <span>Мы оставляем за собой право изменять условия данной Политики конфиденциальности без предварительного уведомления.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <span class="me-2 text-primary fw-medium">6.2.</span>
                        <span>Все изменения публикуются на этой странице с указанием даты вступления в силу.</span>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Финальная плашка безопасности --}}
        <div class="mt-5 p-3 rounded-3 bg-success bg-opacity-10 text-success border border-success border-opacity-25 d-flex align-items-center">
            <div class="me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.6.6 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.656-.242-1.712-.6-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7.2 7.2 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.2 7.2 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56z"/>
                    <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
            <span class="small fw-medium">Все данные, передаваемые через наш сайт, надежно защищены с использованием современных методов шифрования.</span>
        </div>

    </div>
</div>

@include('layouts.footer')
@endsection