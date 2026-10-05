@if($topItems->isEmpty())
  {{-- В городе пока нет клиник с отзывами: другие города здесь не показываем --}}
  <section class="slider_section">
    <div class="container">
      @if(!empty($recommendationCity))
        <h3 class="top_rank_doctor_h3">Рекомендации для вас в городе {{ $recommendationCity }}</h3>
      @else
        <h3 class="top_rank_doctor_h3">Рекомендации для вас</h3>
      @endif

      <div style="text-align:center;padding:36px 20px;margin-top:14px;background:#fff;border:1px dashed #e5e7eb;border-radius:16px;">
        <div style="font-size:44px;line-height:1;margin-bottom:10px;" aria-hidden="true">⭐</div>

        @if(!empty($recommendationCity))
          <p style="font-size:20px;font-weight:700;color:#1f2937;margin:0 0 6px;">Пока в вашем городе нет отзывов</p>
          <p style="font-size:16px;color:#6b7280;margin:0 0 18px;">Напишите первый — выберите клинику и поделитесь впечатлением.</p>
        @else
          <p style="font-size:20px;font-weight:700;color:#1f2937;margin:0 0 6px;">Выберите город</p>
          <p style="font-size:16px;color:#6b7280;margin:0 0 18px;">Укажите город в шапке сайта — и мы покажем лучшие клиники рядом с вами.</p>
        @endif

        <a href="{{ route('clinics.index') }}"
           style="display:inline-block;background:#ff8c00;color:#fff;font-weight:600;border-radius:25px;padding:11px 30px;text-decoration:none;">
          Перейти в каталог
        </a>
      </div>
    </div>
  </section>
@else
<section class="slider_section">
  <div class="container">
    <h3 class="top_rank_doctor_h3">Рекомендации для вас в городе {{ $recommendationCity ?? $currentCityName }}</h3>
    <h5 class="top_rank_doctor_h5">лучшие по оценкам пользователей</h5>

    <div class="carousel js-swipe-carousel">
      @foreach($topItems as $index => $item)
        <input
          type="radio"
          name="slides"
          class="carousel-spatial-radio" {{-- 2. ДОБАВИЛИ КЛАСС ДЛЯ РАДИО --}}
          id="slide-clinic-{{ $index }}"
          @checked($index === 0)>
      @endforeach

      <ul class="carousel__slides">
        @foreach($topItems as $item)
          @php
              $image = match ($item->reviewable_type) {
                  'Doctor'       => $item->photo,
                  'Clinic'       => $item->logo,
                  'Organization' => $item->logo,
                  default        => $item->photo ?? null,
              };

              // Клиники и организации живут на /{type}/{city}/{slug} —
              // без city_slug ссылка попадает на URI, зарегистрированный
              // только под PUT/DELETE, и Laravel отдаёт 405.
              $itemUrl = match ($item->reviewable_type) {
                  'Doctor'       => route('doctors.show', $item->slug),
                  'Specialist'   => route('specialists.show', $item->slug),
                  'Clinic'       => route('clinics.show', ['city' => $item->city_slug, 'clinic' => $item->slug]),
                  'Organization' => route('organizations.show', ['city' => $item->city_slug, 'slug' => $item->slug]),
                  default        => url(strtolower($item->reviewable_type).'s/'.$item->slug),
              };
          @endphp

          <li class="carousel__slide">
              <figure>
                  <div>
                      <a href="{{ $itemUrl }}">
                          <img
                              class="carousel__slide_img_prewiew"
                              src="{{ $image ? asset('storage/'.$image) : asset('storage/clinics/logo/default-clinic.webp') }}"
                              alt="{{ $item->name }}"
                          >
                      </a>
                  </div>

                  <figcaption>
                    <a href="{{ $itemUrl }}">
                      {{ $item->name }}
                    </a>
                    <a href="{{ $itemUrl }}?tab=reviews">
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
                  'Doctor'       => $item->photo,
                  'Clinic'       => $item->logo,
                  'Organization' => $item->logo,
                  default        => $item->photo ?? null,
              };
          @endphp

          <li>
              <label for="slide-clinic-{{ $index }}">
                  <img
                    class="carousel__slide_img"
                    src="{{ $image ? asset('storage/'.$image) : asset('storage/clinics/logo/default-clinic.webp') }}"
                    alt="{{ $item->name }}"
                  >
              </label>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('.js-swipe-carousel');
    
    if (!carousel) return;

    // Клик по миниатюре внизу — это <label for="..."> для скрытого radio.
    // Браузер по умолчанию при активации label переводит фокус на связанный
    // input и докручивает страницу так, чтобы он оказался в зоне видимости.
    // Наш input визуально скрыт (clip/position:absolute, а не display:none),
    // поэтому он формально виден для браузера — отсюда и прыжок скролла.
    // Переключаем слайд вручную, без нативной активации input, чтобы
    // скролла не было вообще.
    const thumbLabels = carousel.querySelectorAll('.carousel__thumbnails label');
    thumbLabels.forEach(label => {
        label.addEventListener('click', (e) => {
            e.preventDefault();

            const input = document.getElementById(label.getAttribute('for'));
            if (!input || input.checked) return;

            input.checked = true;
            // Радио слушает 'change', чтобы перезапустить автопрокрутку
            // (см. resources/js/slider/personal_recommendations.js) —
            // программная установка .checked его не вызывает сама по себе.
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    let touchStartX = 0;
    let touchEndX = 0;

    // Минимальная дистанция в пикселях, чтобы жест считался свайпом
    const swipeThreshold = 50; 

    // Ловим начало касания
    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    // Ловим окончание касания
    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    // Функция обработки жеста
    function handleSwipe() {
        const radios = Array.from(carousel.querySelectorAll('.carousel-spatial-radio'));
        if (radios.length <= 1) return;

        // Находим индекс текущего активного слайда
        const currentIndex = radios.findIndex(radio => radio.checked);
        
        // Вычисляем разницу движения пальца
        const swipeDistance = touchEndX - touchStartX;

        if (swipeDistance < -swipeThreshold) {
            // Свайп ВЛЕВО -> Показываем СЛЕДУЮЩИЙ слайд
            const nextIndex = (currentIndex + 1) % radios.length;
            radios[nextIndex].checked = true;
        } else if (swipeDistance > swipeThreshold) {
            // Свайп ВПРАВО -> Показываем ПРЕДЫДУЩИЙ слайд
            const prevIndex = (currentIndex - 1 + radios.length) % radios.length;
            radios[prevIndex].checked = true;
        }
    }
});
</script>
@endif
