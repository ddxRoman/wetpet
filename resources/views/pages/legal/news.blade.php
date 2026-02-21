@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
<title>Новости</title>
@extends('layouts.app')


@section('content')
<div class="header_in_account">
    @include('layouts.header')
</div>
<div class="container">
    <h2 class="legal_h2">Новости</h2>
<p>
    тут надо делать карточки с нвоостями
</p>
</div>
    @include('layouts.footer')
@endsection