<section class="slider_section">
  <div class="container">
    <h3 class="top_rank_doctor_h3">Рекомендации для вас в городе {{ $currentCityName }}</h3>
    <h5 class="top_rank_doctor_h5">лучшие по оценкам пользователей</h5>

    <div class="carousel">
      @foreach($topItems->take(6) as $index => $item)
        <input
          type="radio"
          name="slides"
          id="slide-clinic-{{ $index }}"
          @checked($index === 0)>
      @endforeach

      <ul class="carousel__slides">
        @foreach($topItems->take(6) as $item)
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
                      <a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->slug) }}">
                          <img
                              class="carousel__slide_img_prewiew"
                              src="{{ $image ? asset('storage/'.$image) : asset('storage/clinics/logo/default.webp') }}"
                              alt="{{ $item->name }}"
                          >
                      </a>
                  </div>

                  <figcaption>
                    <a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->slug) }}">
                      {{ $item->name }}
                    </a>
                    <a href="{{ url(strtolower($item->reviewable_type).'s/'.$item->slug) }}?tab=reviews">
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
        @foreach($topItems->take(6) as $index => $item)
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
                    src="{{ $image ? asset('storage/'.$image) : asset('storage/clinics/logo/default.webp') }}"
                    alt="{{ $item->name }}"
                  >
              </label>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>