@extends('layouts.app')

@section('content')
<div class="header_in_account">
    @include('layouts.header')
</div>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-lg-8">
                
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Главная</a></li>
                        <li class="breadcrumb-item active text-dark" aria-current="page">Конфиденциальность</li>
                    </ol>
                </nav>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    
                    <h1 class="h2 mb-4 text-dark fw-bold position-relative pb-3" style="border-bottom: 2px solid #ffc107;">
                        Согласие на обработку персональных данных
                    </h1>

                    <div class="text-secondary lh-lg fs-6">
                        <p class="mb-4">
                            Я, пользователь сайта <a href="https://zverozor.ru/" class="text-decoration-none fw-medium" style="color: #0d6efd;">https://zverozor.ru/</a>, 
                            даю своё согласие {{ config('company.fio') }} ИНН: {{ config('company.inn') }}, зарегистрированному по адресу: {{ config('company.adress') }}  на обработку моих персональных данных в целях:
                        </p>

                        <ul class="list-unstyled mb-4 ps-0">
                            <li class="d-flex align-items-start mb-3">
                                <span class="badge bg-warning text-dark me-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; min-width: 24px;">✓</span>
                                <span>заключения и исполнения договоров и оказания услуг;</span>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <span class="badge bg-warning text-dark me-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; min-width: 24px;">✓</span>
                                <span>обратной связи и обработки обращений;</span>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <span class="badge bg-warning text-dark me-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; min-width: 24px;">✓</span>
                                <span>информирования о новостях, акциях и специальных предложениях (при отдельном подтверждении);</span>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <span class="badge bg-warning text-dark me-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; min-width: 24px;">✓</span>
                                <span>анализа посещаемости сайта с использованием сервиса «Яндекс.Метрика» для улучшения качества работы сайта;</span>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <span class="badge bg-warning text-dark me-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; min-width: 24px;">✓</span>
                                <span>выполнения обязанностей, установленных законодательством Российской Федерации.</span>
                            </li>
                        </ul>

                        <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-warning">
                            <p class="mb-0 small text-muted">
                                <strong>Обрабатываемые данные могут включать:</strong> фамилию, имя, отчество, контактные данные, сведения, предоставленные добровольно пользователем, и данные, получаемые автоматически через сервисы аналитики (например, IP-адрес, cookie).
                            </p>
                        </div>

                        <p class="mb-3">
                            <strong>Срок действия согласия:</strong> до достижения указанных целей обработки или до отзыва согласия пользователем.
                        </p>

                        <p class="mb-4">
                            Согласие может быть отозвано в любой момент путём направления уведомления на электронную почту Оператора: 
                            <a href="mailto:info@zverozor.ru" class="fw-bold text-decoration-none style-link" style="color: #212529;">{{ config('company.email') }}</a>.
                        </p>

                        <div class="text-center pt-3 border-top mt-4">
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fs-7">
                                Настоящее согласие оформлено в электронной форме и не требует заполнения личных данных пользователем вручную.
                            </span>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>
    </div>
</main>

@include('layouts.footer')
@endsection