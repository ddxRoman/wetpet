<style>
/* ====== ДЕСКТОП — без изменений ====== */
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

.container_slider {
  position: relative;
  width: 100%;
  margin: 0 auto;
}

.wgh-slider {
  position: relative;
}

.wgh-slider__viewport {
  overflow: hidden;
}

.wgh-slider-item-figure__image {
  width: 100%;
  border-radius: 12px;
}

.wgh-slider-item-figure__caption {
  margin-top: 10px;
  font-size: 1rem;
  color: #333;
}

/* ====== МОБИЛЬНАЯ ВЕРСИЯ ====== */
@media (max-width: 992px) {
  /* Прячем radio-инпуты и механику слайдера */
  .wgh-slider-target,
  .wgh-slider-item__trigger {
    display: none !important;
  }

  /* Превращаем слайдер в горизонтальную прокрутку */
  .wgh-slider__viewport {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    display: flex;
    gap: 16px;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    padding: 0 10px;
  }

  .wgh-slider__container {
    display: flex;
    flex-wrap: nowrap;
    gap: 16px;
  }

  .wgh-slider-item {
    flex: 0 0 80%;
    scroll-snap-align: center;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
  }

  .wgh-slider-item-figure__image {
    height: 220px;
    object-fit: cover;
    border-radius: 15px 15px 0 0;
  }

  .wgh-slider-item-figure__caption {
    font-size: 0.9rem;
    padding: 10px;
  }

  .wgh-slider-item-figure__caption a {
    display: block;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    margin-bottom: 5px;
  }

  .top_rank_doctor_h3 {
    font-size: 1.5rem;
  }

  .top_rank_doctor_h5 {
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .wgh-slider-item {
    flex: 0 0 90%;
  }

  .wgh-slider-item-figure__image {
    height: 180px;
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
  <div class="rt-container">
    <h3 class="top_rank_doctor_h3">Лучшие ветеринары города {{ $currentCityName }}</h3>
    <h5 class="top_rank_doctor_h5">по оценкам пользователей</h5>
    <div class="col-rt-12">
      <div class="Scriptcontent">
        <div class="container_slider">
          <div class="wgh-slider">
            <!-- radio-инпуты остаются для десктопа -->
            <input class="wgh-slider-target" type="radio" id="slide-doctor-1" name="slider" checked="checked"/>
            <input class="wgh-slider-target" type="radio" id="slide-doctor-2" name="slider"/>
            <input class="wgh-slider-target" type="radio" id="slide-doctor-3" name="slider"/>
            <input class="wgh-slider-target" type="radio" id="slide-doctor-4" name="slider"/>
            <input class="wgh-slider-target" type="radio" id="slide-doctor-5" name="slider"/>

            <div class="wgh-slider__viewport">
              <div class="wgh-slider__viewbox">
                <div class="wgh-slider__container">
                  <div class="wgh-slider-item">
                    <div class="wgh-slider-item__inner">
                      <figure class="wgh-slider-item-figure">
                        <img class="wgh-slider-item-figure__image" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="Ветеринар 1"/>
                        <figcaption class="wgh-slider-item-figure__caption">
                          <a href="page_doctor">Первая картинка</a>
                          <span>Quantic</span>
                        </figcaption>
                      </figure>
                      <label class="wgh-slider-item__trigger" for="slide-doctor-1"></label>
                    </div>
                  </div>

                  <div class="wgh-slider-item">
                    <div class="wgh-slider-item__inner">
                      <figure class="wgh-slider-item-figure">
                        <img class="wgh-slider-item-figure__image" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="Ветеринар 2"/>
                        <figcaption class="wgh-slider-item-figure__caption">
                          <a href="page_doctor">Центральная картинка</a>
                          <span>Quantic</span>
                        </figcaption>
                      </figure>
                      <label class="wgh-slider-item__trigger" for="slide-doctor-2"></label>
                    </div>
                  </div>

                  <div class="wgh-slider-item">
                    <div class="wgh-slider-item__inner">
                      <figure class="wgh-slider-item-figure">
                        <img class="wgh-slider-item-figure__image" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="Ветеринар 3"/>
                        <figcaption class="wgh-slider-item-figure__caption">
                          <a href="page_doctor">Третья картинка</a>
                          <span>Quantic</span>
                        </figcaption>
                      </figure>
                      <label class="wgh-slider-item__trigger" for="slide-doctor-3"></label>
                    </div>
                  </div>

                  <div class="wgh-slider-item">
                    <div class="wgh-slider-item__inner">
                      <figure class="wgh-slider-item-figure">
                        <img class="wgh-slider-item-figure__image" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="Ветеринар 4"/>
                        <figcaption class="wgh-slider-item-figure__caption">
                          <a href="page_doctor">Четвёртая картинка</a>
                          <span>Quantic</span>
                        </figcaption>
                      </figure>
                      <label class="wgh-slider-item__trigger" for="slide-doctor-4"></label>
                    </div>
                  </div>

                  <div class="wgh-slider-item">
                    <div class="wgh-slider-item__inner">
                      <figure class="wgh-slider-item-figure">
                        <img class="wgh-slider-item-figure__image" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="Ветеринар 5"/>
                        <figcaption class="wgh-slider-item-figure__caption">
                          <a href="page_doctor">Пятая картинка</a>
                          <span>Quantic</span>
                        </figcaption>
                      </figure>
                      <label class="wgh-slider-item__trigger" for="slide-doctor-5"></label>
                    </div>
                  </div>
                </div>
              </div>
            </div> <!-- /.wgh-slider__viewport -->
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
