@vite(['resources/css/main.css','resources/css/slider.css','resources/sass/app.scss', 'resources/js/app.js'])

<style>
    .slider_section{
    background-color: pink;
    margin-top: 1%;

    }
</style>

    <div class="container">
        <div class="row">

            <div class="col-2">
                <div class="div_btn_category">
                    Животные
                </div>
            </div>
            <div class="col-2">
                <div class="div_btn_category"> Клиники</div>
            </div>
            <div class="col-2">
                <div class="div_btn_category"> Зооцентры</div>
            </div>
            <div class="col-2">
                <div class="div_btn_category">Тосё</div>
            </div>
            <div class="col-2">
                <div class="div_btn_category">Пятое Десятое</div>
            </div>
            <div class="col-2">
                <div class="div_btn_category">Все остальное</div>
            </div>
        </div>
        <div class="row">
            <h2 class="header_h2">Популярные Врачи в городе <button type="button" class="btn_popular_in_city" data-bs-toggle="modal" data-bs-target="#selectcityModal">[city]</button></h2>
<div class="col-4">
    <div class="most_popular_in_city">
    <figcaption class="title_list_popular">Врачи</figcaption>
    <ul class="list_doctor"> 
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Специализация</a> 
        <span class="list_specialist_count">50</span>
    </li>
    </div>
</div>
<div class="col-4">
    <div class="most_popular_in_city">
    <figcaption class="title_list_popular">Вет центры</figcaption>
    <ul class="list_doctor"> 
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Названия клиники</a> 
        <span class="list_specialist_count">50</span>
    </li>
    </ul>
    </div>
</div>
<div class="col-4">
    <div class="most_popular_in_city">
    <figcaption class="title_list_popular">Зоомагазины</figcaption>
    <ul class="list_doctor"> 
        <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="">Названия магазина</a> 
        <span class="list_specialist_count">50</span>
    </li>
    </ul>
    </div>
</div>
</ul>
@extends('layouts.modules.top_rank_clinic')
@section('top_rank_clinic')
@endsection
@extends('layouts.modules.must_popular')



</div>
    </div>
