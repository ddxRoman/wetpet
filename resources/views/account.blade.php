@extends('layouts.app')
@vite([
    'resources/css/main.css',
    'resources/sass/app.scss',
    'resources/js/app.js',
    'resources/js/account/account.js'
])




<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>



@section('content')
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет {{$user->nickname}}</title>
</head>
<style>
    .py-4 { padding: 0 !important; }
    #avatar-input { display: none; }
</style>

<body class="body_page">
    {{-- 🔹 Навбар --}}
    <nav class="navbar">
        <div class="navbar-logo">
            <a href="{{ url('/') }}">
                <img src="{{ Storage::url('logo/logo3.png') }}" alt="Логотип">
            </a>
        </div>
        <div class="navbar-user">
            {{ Auth::user()->nickname ?? Auth::user()->name }}
        </div>
    </nav>

    <div class="account-container">
        <div class="sidebar">
            <button  class="tab-btn active" data-tab="profile">Профиль</button>
            <button  class="tab-btn" data-tab="pets">Питомцы</button>
            <!-- <button class="tab-btn" data-tab="favorits">Избранное</button> -->
            <button  class="tab-btn" data-tab="reviews">Отзывы</button>
        </div>

        <div class="account-content">
            {{-- 🔹 Вкладка профиля --}}
            <div class="tab-content" id="profile">
                <h2 title="{{ $user->name }}">Профиль {{ $user->nickname ?? '' }}</h2>
                <p>С нами с {{ $user->created_at->format('d.m.Y') }}</p>

                <form method="POST" action="{{ route('account.updateProfile') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group avatar-upload">
                        <label for="avatar-input">
                            <img title="Изменить фото"
                                 src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('storage/default-avatar.png') }}"
                                 alt="Аватар"
                                 class="avatar-preview"
                                 id="avatar-preview"
                                 style="cursor:pointer;">
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>ФИО *</label>
                        <input type="text" name="name" value="{{ $user->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Ник *</label>
                        <input type="text" name="nickname" value="{{ $user->nickname ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" name="birth_date" value="{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '' }}">

                    </div>

                    <div class="form-group">
                        <label>Город</label>
                        <select id="city-select" name="city_slug" style="width:100%;"></select>

                        <div id="new-city-fields" style="display:none; margin-top:15px;">
                            <input type="text" id="new-country" placeholder="Страна" style="margin-bottom:8px;">
                            <input type="text" id="new-region" placeholder="Область / Край" style="margin-bottom:8px;">
                            <input type="text" id="new-name" placeholder="Название города">
                            <button type="button" id="save-city-btn"
                                style="margin-top:10px; background:#007bff; color:#fff; border:none; padding:8px 14px; border-radius:6px;">
                                Сохранить город
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Почта *</label>
                        <input type="email" name="email" value="{{ $user->email }}" required>
                    </div>

                    <button type="submit" class="save-btn">Сохранить изменения</button>
                </form>
            </div>

            {{-- 🔹 Вкладка питомцев --}}
            <div class="tab-content" id="pets" style="display:none;">
                <h2>Мои питомцы</h2>
                <button id="add-pet-btn" class="save-btn" style="margin-bottom:15px;">+ Добавить питомца</button>

<form id="add-pet-form" enctype="multipart/form-data" style="display:none; margin-bottom:20px;">
    <select id="type-select" name="type" style="width:100%; margin-bottom:10px;">
        <option value="">Выберите тип животного...</option>
    </select>

    <select id="breed-select" name="breed" style="width:100%; margin-bottom:10px;" disabled>
        <option value="">Сначала выберите тип...</option>
    </select>

    <input type="text" id="pet-name" name="name" placeholder="Имя питомца" style="width:100%; margin-bottom:10px;">

    <label>Пол питомца:</label>
    <select id="pet-gender" name="gender" style="width:100%; margin-bottom:10px;">
        <option value="">Выберите пол...</option>
        <option value="male">Самец</option>
        <option value="female">Самка</option>
    </select>

    <div id="birth-block">
        <label>Дата рождения:</label>
        <input type="date" id="pet-birth" name="birth" style="width:100%;">
    </div>

    <label style="display:flex; align-items:center; gap:8px; margin-top:8px;">
        <input type="checkbox" id="unknown-birth"> Я не знаю точной даты
    </label>

    <div id="age-block" style="display:none;">
        <label>Возраст:</label>
        <input type="number" id="pet-age" name="age" min="0" style="width:100%;">
    </div>

    <!-- Фото питомца -->
    <label style="display:block; margin-top:10px;">Фото питомца:</label>
    <input type="file" id="pet-photo" name="photo" accept="image/*" style="width:100%; margin-bottom:10px;">
    <img id="photo-preview" src="" alt="" style="max-width:100px; display:none; border-radius:8px; margin-bottom:10px;">

    <button id="save-pet-btn" type="submit" class="save-btn" style="margin-top:10px;">Сохранить</button>
</form>


<div id="pets-list" class="pets-grid"></div>


                <div id="pets-list"></div>
            </div>
            <!-- Отзывы -->
             @include('account.tabs.reviews')
        </div>
    </div>


<!-- 🔹 Модалка редактирования питомца -->
<div id="edit-pet-modal" class="modal" style="
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);
    justify-content:center; align-items:center; z-index:9999;">
  <div style="background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px; position:relative;">
    <button id="close-modal" style="position:absolute; top:8px; right:10px; background:none; border:none; font-size:18px;">✖</button>
    <h3>Редактировать питомца</h3>

    <input type="hidden" id="edit-pet-id">

    <div style="margin-bottom:10px;">
      <label>Имя *</label>
      <input type="text" id="edit-pet-name" style="width:100%;">
    </div>

    <div style="margin-bottom:10px;">
      <label>Порода *</label>
      <select id="edit-pet-breed" style="width:100%;"></select>
    </div>

    <div id="edit-birth-block" style="margin-bottom:10px;">
      <label>Дата рождения</label>
      <input type="date" id="edit-pet-birth" style="width:100%;">
    </div>

    <label style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
      <input type="checkbox" id="edit-unknown-birth"> Я не знаю точную дату
    </label>
    

    <div id="edit-age-block" style="display:none; margin-bottom:10px;">
      <label>Возраст</label>
      <input type="number" id="edit-pet-age" style="width:100%;" min="0">
    </div>

<div style="margin-bottom:10px; text-align:center;">
  <label for="edit-pet-photo" style="cursor:pointer; display:inline-block;">
    <img id="edit-photo-preview"
         src="/storage/pets/default-pet.jpg"
         alt="Фото питомца"
         style="max-width:150px; border-radius:10px; margin-bottom:8px; border:2px solid #ddd; transition:0.3s;">
  </label>
  <input type="file" id="edit-pet-photo" accept="image/*" style="display:none;">
  <p style="font-size:13px; color:#666;">Нажмите на фото, чтобы добавить или изменить</p>
</div>



    <button id="save-edit-pet" class="save-btn">Сохранить изменения</button>



</div>

</div>


<!-- 🔹 Контейнер для уведомлений -->
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:10000;"></div>
<!-- 🔹 Модалка кадрирования фото питомца -->
<div id="cropper-modal" style="
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,0.7); z-index:10000;
    justify-content:center; align-items:center;
">
  <div style="background:#fff; padding:20px; border-radius:10px; max-width:90%; width:400px; text-align:center; position:relative;">
    <button id="close-cropper" style="position:absolute; top:8px; right:10px; background:none; border:none; font-size:18px;">✖</button>
    <h3>Обрезка фото питомца</h3>
    <img id="cropper-image" src="" style="max-width:100%; margin-top:10px; border-radius:8px;">
    <button id="save-cropped" class="save-btn" style="margin-top:15px;">Сохранить</button>
  </div>
</div>

</body>
</html>
@endsection
