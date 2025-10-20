<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://public.codepenassets.com/css/reset-2.0.min.css">
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.7/css/swiper.min.css'>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container slider_doctor">
<div class="row">
<div class="col-12">



  <div class="swiper-container main-slider loading">
    <div class="swiper-wrapper">
      @foreach ($doctors as $doctor)
        <div class="swiper-slide">
          <figure class="slide-bgimg" style="background-image:url('{{ asset($doctor->photo) }}')">
            <img src="{{ asset($doctor->photo) }}" class="entity-img" alt="{{ $doctor->name }}">
          </figure>
          <div class="content">
            <p class="title">{{ $doctor->name }}</p>
            <span class="caption">{{ $doctor->description ?? 'Описание отсутствует' }}</span>
          </div>
        </div>
      @endforeach
    </div>
    <div class="swiper-button-prev swiper-button-white"></div>
    <div class="swiper-button-next swiper-button-white"></div>
  </div>

  <!-- Thumbnail navigation -->
  <div class="swiper-container nav-slider loading">
    <div class="swiper-wrapper" role="navigation">
      @foreach ($doctors as $doctor)
        <div class="swiper-slide">
          <figure class="slide-bgimg" style="background-image:url('{{ asset($doctor->photo) }}')">
            <img src="{{ asset($doctor->photo) }}" class="entity-img" alt="{{ $doctor->name }}">
          </figure>
          <div class="content">
            <p class="title">{{ $doctor->name }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
</div>
</div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.7/js/swiper.min.js'></script>
<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
