
<style>
/* ====== Базовые стили ====== */
.slider_section {
  padding: 50px 0;
  background: #fff;
  text-align: center;
}

.top_rank_doctor_h3 {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.top_rank_doctor_h5 {
  font-size: 1rem;
  font-weight: 400;
  color: #777;
  margin-bottom: 30px;
}

.carousel {
  position: relative;
  width: 100%;
  max-width: 1000px;
  margin: 0 auto;
}

/* ====== Слайды ====== */
.carousel__slides {
  list-style: none;
  padding: 0;
  margin: 0;
}

.carousel__slide figure {
  margin: 0;
}

/* Главное изображение */
.carousel__slide_img_prewiew {
  width: 75%;
  height: 350px;
  aspect-ratio: 2 / 1;
  object-fit: cover;
  border-radius: 15px;
  margin: 0 auto;
  display: block;
}

/* ====== Миниатюры ====== */
.carousel__thumbnails {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 15px;
}

.carousel__thumbnails li {
  display: inline-block;
}

.carousel__slide_img {
  width: 60px;               /* ~75% от прежних */
  aspect-ratio: 1 / 1;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  opacity: 0.8;
  transition: opacity 0.3s;
}

.carousel__slide_img:hover {
  opacity: 1;
}

/* ====== Текст ====== */
figcaption {
  font-size: 1rem;
  margin-top: 10px;
  color: #333;
}

.credit {
  display: block;
  font-size: 0.85rem;
  color: #777;
}

/* ====== Планшеты и мобилки ====== */
@media (max-width: 992px) {
  .carousel {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    display: flex;
    gap: 16px;
    scroll-behavior: smooth;
    padding: 0 10px;
  }

  /* Прячем radio и миниатюры */
  .carousel input,
  .carousel__thumbnails {
    display: none;
  }

  .carousel__slides {
    display: flex;
    flex-wrap: nowrap;
    gap: 16px;
  }

  .carousel__slide {
    flex: 0 0 80%;
    scroll-snap-align: center;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
  }

  .carousel__slide_img_prewiew {
    width: 85%;
  }

  figcaption {
    padding: 10px;
    font-size: 0.9rem;
  }

  .credit {
    font-size: 0.8rem;
  }

  .top_rank_doctor_h3 {
    font-size: 1.5rem;
  }

  .top_rank_doctor_h5 {
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .carousel__slide {
    flex: 0 0 90%;
  }

  .carousel__slide_img_prewiew {
    width: 90%;
  }

  .top_rank_doctor_h3 {
    font-size: 1.3rem;
  }

  .top_rank_doctor_h5 {
    font-size: 0.85rem;
  }
}

</style>

<section class="slider_section">
  <div class="container">
    <h3 class="top_rank_doctor_h3">Рекомендации для вас в городе {{ $currentCityName }}</h3>
    <h5 class="top_rank_doctor_h5">лучшие по оценкам пользователей</h5>

    <div class="carousel">
      <!-- radio-инпуты остаются для десктопа -->
@foreach($topItems as $index => $item)
  <input
    type="radio"
    name="slides"
    id="slide-clinic-{{ $index }}"
    @checked($index === 0)>
@endforeach


      <ul class="carousel__slides">
@foreach($topItems as $item)
    @php
        $image = match ($item->reviewable_type) {
            'Doctor' => $item->photo,
            'Clinic' => $item->logo,
            default  => $item->photo ?? null,
        };
    @endphp

    <li class="carousel__slide">
        <figure>
            <div>
                <a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->id) }}">
                    <img
                        class="carousel__slide_img_prewiew"
                        src="{{ $image ? asset('storage/'.$image) : asset('images/no-photo.jpg') }}"
                        alt="{{ $item->name }}"
                    >
                </a>
            </div>

            <figcaption>
              <a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->id) }}">
                {{ $item->name }}
</a>
<a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->id) }}?tab=reviews">

                <span class="credit">
                    ⭐ {{ $item->avg_rating }} / 5
                    ({{ $item->reviews_count }} отзывов)
                </span>
</a>
                <div style="margin-top:5px;">
                    {{ Str::limit($item->description, 80) }}
                </div>
                
            </figcaption>
        </figure>
    </li>
@endforeach
</ul>


<ul class="carousel__thumbnails">
@foreach($topItems as $index => $item)
    @php
        $image = match ($item->reviewable_type) {
            'Doctor' => $item->photo,
            'Clinic' => $item->logo,
            default  => $item->photo ?? null,
        };
    @endphp

    <li>
        <label for="slide-clinic-{{ $index }}">
            <img
              class="carousel__slide_img"
              src="{{ $image ? asset('storage/'.$image) : asset('images/no-photo-thumb.jpg') }}"
              alt="{{ $item->name }}"
            >
        </label>
    </li>
@endforeach
</ul>


    </div>
  </div>
</section>
    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])
