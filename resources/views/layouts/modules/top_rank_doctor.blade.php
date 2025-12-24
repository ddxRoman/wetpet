<!-- Слайдер который не хочет работать нормально -->












<!-- Слайдер который не хочет работать нормально -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://public.codepenassets.com/css/reset-2.0.min.css">
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.7/css/swiper.min.css'>
</head>
<body>
<div class="container slider_doctor slider_doctor_mobile">
  <div class="row row_slider_doctor row_slider_doctor_mobile">
    <div class="col-12 col_slider_doctor col_slider_doctor_mobile">

      <!-- Основной слайдер -->
      <div class="swiper-container main-slider loading main-slider_mobile">
        <div class="swiper-wrapper">
          @foreach ($doctors as $doctor)
            <div class="swiper-slide swiper-slide_mobile">
              <figure
    class="slide-bgimg slide-bgimg_mobile"
    style="background-image:url('{{ $doctor->photo
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.png') }}')">

                <img
    src="{{ $doctor->photo
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.png') }}"
    class="entity-img entity-img_mobile"
    alt="{{ $doctor->name }}"
>

              </figure>
              <div class="content content_mobile">
                <p class="title title_mobile">{{ $doctor->name }}</p>
                <span class="caption caption_mobile">{{ $doctor->description ?? 'Описание отсутствует' }}</span>
              </div>
            </div>
          @endforeach
        </div>
        <div class="swiper-button-prev swiper-button-white swiper-button-prev_mobile"></div>
        <div class="swiper-button-next swiper-button-white swiper-button-next_mobile"></div>
      </div>

      <!-- Миниатюры -->
      <div class="swiper-container nav-slider loading nav-slider_mobile">
        <div class="swiper-wrapper" role="navigation">
          @foreach ($doctors as $doctor)
            <div class="swiper-slide swiper-slide_mobile_thumb">
              <figure
    class="slide-bgimg slide-bgimg_mobile_thumb"
    style="background-image:url('{{ $doctor->photo
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.png') }}')">

                <img
    src="{{ $doctor->photo
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.png') }}"
    class="entity-img entity-img_mobile"
    alt="{{ $doctor->name }}">
              </figure>
              <div class="content content_mobile_thumb">
                <p class="title title_mobile_thumb">{{ $doctor->name }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
