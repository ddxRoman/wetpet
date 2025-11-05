<style>
/* ====== Базовые стили (десктоп — без изменений) ====== */
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

.carousel__slides {
  list-style: none;
  padding: 0;
  margin: 0;
}

.carousel__slide figure {
  margin: 0;
}

.carousel__slide_img_prewiew {
  width: 100%;
  height: 400px;
  object-fit: cover;
  border-radius: 15px;
}

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
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  opacity: 0.8;
  transition: 0.3s;
}

.carousel__slide_img:hover {
  opacity: 1;
}

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

/* ====== Мобильная адаптация ====== */
@media (max-width: 992px) {
  .carousel {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    display: flex;
    gap: 16px;
    scroll-behavior: smooth;
    padding: 0 10px;
  }

  /* Прячем radio-инпуты и миниатюры — они не нужны на телефоне */
  .carousel input,
  .carousel__thumbnails {
    display: none;
  }

  /* Слайды становятся горизонтально прокручиваемыми */
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
    height: 220px;
    border-radius: 15px 15px 0 0;
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
  <div class="container">
    <h3 class="top_rank_doctor_h3">Рекомендации для вас в городе {{ $currentCityName }}</h3>
    <h5 class="top_rank_doctor_h5">по оценкам пользователей</h5>

    <div class="carousel">
      <!-- radio-инпуты остаются для десктопа -->
      <input type="radio" name="slides" checked="checked" id="slide-clinic-1">
      <input type="radio" name="slides" id="slide-clinic-2">
      <input type="radio" name="slides" id="slide-clinic-3">
      <input type="radio" name="slides" id="slide-clinic-4">
      <input type="radio" name="slides" id="slide-clinic-5">
      <input type="radio" name="slides" id="slide-clinic-6">

      <ul class="carousel__slides">
        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://avatars.mds.yandex.net/get-altay/11403733/2a0000018e5aae6202ced7c736dc57a5e53e/XXL_height" alt="">
              </a>
            </div>
            <figcaption>
              Гепард Васильевич
              <span class="credit">Лечит лень, заставляет бегать</span>
            </figcaption>
          </figure>
        </li>

        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt="">
              </a>
            </div>
            <figcaption>
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
              <span class="credit">Photo: Christian Joudrey</span>
            </figcaption>
          </figure>
        </li>

        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://p0.zoon.ru/preview/BiAdyyks6Ez9i0dc5w-kbg/2400x1500x85/1/1/3/original_6035bfbd8aece54aed2aa414_603613435ea85.jpg" alt="">
              </a>
            </div>
            <figcaption>
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
              <span class="credit">Photo: Steve Carter</span>
            </figcaption>
          </figure>
        </li>

        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://gas-kvas.com/uploads/posts/2023-02/1676904858_gas-kvas-com-p-risunok-na-temu-ya-khochu-stat-veterinarom-9.jpg" alt="">
              </a>
            </div>
            <figcaption>
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
              <span class="credit">Photo: Aleksandra Boguslawska</span>
            </figcaption>
          </figure>
        </li>

        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://wallpapers.com/images/hd/cute-kawaii-fox-in-hollow-log-2qyihxo0dlmrvwzh.jpg" alt="">
              </a>
            </div>
            <figcaption>
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
              <span class="credit">Photo: Rosan Harmens</span>
            </figcaption>
          </figure>
        </li>

        <li class="carousel__slide">
          <figure>
            <div>
              <a href="">
                <img class="carousel__slide_img_prewiew" src="https://avatars.mds.yandex.net/i?id=11c80c4e000e835751f538ee6c92efa2_l-5222428-images-thumbs&n=13" alt="">
              </a>
            </div>
            <figcaption>
              Lorem ipsum dolor sit amet consectetur adipisicing elit.
              <span class="credit">Photo: Annie Spratt</span>
            </figcaption>
          </figure>
        </li>
      </ul>

      <ul class="carousel__thumbnails">
        <li><label for="slide-clinic-1"><img class="carousel__slide_img" src="https://avatars.mds.yandex.net/get-altay/11403733/2a0000018e5aae6202ced7c736dc57a5e53e/XXL_height" alt=""></label></li>
        <li><label for="slide-clinic-2"><img class="carousel__slide_img" src="https://i.pinimg.com/736x/d3/ea/76/d3ea76b935336a9be4af78961d9cf898.jpg" alt=""></label></li>
        <li><label for="slide-clinic-3"><img class="carousel__slide_img" src="https://p0.zoon.ru/preview/BiAdyyks6Ez9i0dc5w-kbg/2400x1500x85/1/1/3/original_6035bfbd8aece54aed2aa414_603613435ea85.jpg" alt=""></label></li>
        <li><label for="slide-clinic-4"><img class="carousel__slide_img" src="https://gas-kvas.com/uploads/posts/2023-02/1676904858_gas-kvas-com-p-risunok-na-temu-ya-khochu-stat-veterinarom-9.jpg" alt=""></label></li>
        <li><label for="slide-clinic-5"><img class="carousel__slide_img" src="https://wallpapers.com/images/hd/cute-kawaii-fox-in-hollow-log-2qyihxo0dlmrvwzh.jpg" alt=""></label></li>
        <li><label for="slide-clinic-6"><img class="carousel__slide_img" src="https://avatars.mds.yandex.net/i?id=11c80c4e000e835751f538ee6c92efa2_l-5222428-images-thumbs&n=13" alt=""></label></li>
      </ul>
    </div>
  </div>
</section>
