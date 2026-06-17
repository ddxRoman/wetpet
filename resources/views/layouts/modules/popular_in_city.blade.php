<!-- Бургер я починил, теперь давай оживим этот блок. У меня для него ничего нет. Это должно быть у клиентов которые купили 
платную подписку, Тогда их обьявления будут размещатся в этом блоке. Сейчас у меня кроме вёрстки ничего нет, ни таблиц, 
ни системы оплаты, ни кабинета для этого -->




<style>
/* === ОБЩИЕ КОММЕРЧЕСКИЕ СТИЛИ ДЛЯ ОБОИХ БЛОКОВ === */
.badge-discount {
    background-color: #dc3545;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 6px;
}
.commercial-old-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.85rem;
    margin-right: 5px;
}
.commercial-new-price {
    color: #28a745;
    font-weight: 700;
}
.btn-commercial {
    font-size: 0.85rem;
    padding: 5px 12px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

/* === ДЕСКТОП — как было === */
@media (min-width: 993px) {
  .mobile-popular-wrapper {
    display: none !important;
  }
  .commercial-card-desktop {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .commercial-card-desktop:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  }
}

/* === МОБИЛЬНАЯ ВЕРСИЯ — адаптация под акции === */
@media (max-width: 992px) {
  .desktop-popular-wrapper {
    display: none !important;
  }

  .mobile-popular-wrapper {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 15px;
  }

  .mobile-popular-title {
    font-size: 1.3rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 10px;
    color: #222;
  }

  .mobile-popular-scroll {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    gap: 15px;
    padding: 10px 5px;
  }

  .mobile-popular-card {
    flex: 0 0 85%;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 15px;
    scroll-snap-align: center;
    border: 1px solid #f0f0f0;
  }

  .mobile-popular-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 8px;
  }

  .mobile-popular-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .mobile-popular-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid #eef0f2;
  }

  .mobile-popular-link {
    text-decoration: none;
    color: #212529;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.3;
  }
  
  .mobile-clinic-name {
    font-size: 0.8rem;
    color: #6c757d;
  }

  .mobile-commercial-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 5px;
    border-top: 1px dashed #e0e0e0;
    padding-top: 8px;
  }
}
</style>

<div class="container desktop-popular-wrapper my-4">
  <div class="row">
    <h2 class="header_h2 mb-4">Выгодные предложения в г. {{ $currentCityName }}</h2>

    <div class="col-4">
      <div class="most_popular_in_city border commercial-card-desktop">
        <figcaption class="title_list_popular d-flex justify-content-between align-items-center">
          <span>Скидки на услуги</span>
          <span class="badge-discount">% Акции</span>
        </figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2 border-bottom">
            <a class="link_in_category_specialist font-weight-bold" href="" title="Стерилизация кошки">Комплексная вакцинация</a>
            <small class="text-muted">Ветклиника «Айболит»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">2 500 ₽</span>
                <span class="commercial-new-price">1 800 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Забрать</a>
            </div>
          </li>
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2">
            <a class="link_in_category_specialist font-weight-bold" href="" title="УЗИ брюшной полости">УЗИ брюшной полости</a>
            <small class="text-muted">Центр «Счастливая морда»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">1 800 ₽</span>
                <span class="commercial-new-price">1 350 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Забрать</a>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="col-4">
      <div class="most_popular_in_city border commercial-card-desktop">
        <figcaption class="title_list_popular d-flex justify-content-between align-items-center">
          <span>Диагностика и Чек-апы</span>
          <span class="badge text-white bg-success" style="font-size:0.75rem;">Топ цена</span>
        </figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2 border-bottom">
            <a class="link_in_category_specialist font-weight-bold" href="" title="Чек-ап пожилых собак">Чек-ап «Здоровый котенок»</a>
            <small class="text-muted">Ветцентр «ЗооДоктор»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">4 000 ₽</span>
                <span class="commercial-new-price">2 900 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Подробнее</a>
            </div>
          </li>
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2">
            <a class="link_in_category_specialist font-weight-bold" href="" title="Чистка зубов ультразвуком">УЗ-чистка зубов</a>
            <small class="text-muted">Клиника «Вега»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">3 500 ₽</span>
                <span class="commercial-new-price">2 490 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Подробнее</a>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="col-4">
      <div class="most_popular_in_city border commercial-card-desktop">
        <figcaption class="title_list_popular d-flex justify-content-between align-items-center">
          <span>Груминг и Уход</span>
          <span class="badge-discount">-30%</span>
        </figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2 border-bottom">
            <a class="link_in_category_specialist font-weight-bold" href="" title="Стрижка йорка комплекс">Комплекс для Йорка</a>
            <small class="text-muted">Салон «Красивый Хвост»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">2 200 ₽</span>
                <span class="commercial-new-price">1 650 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Запись</a>
            </div>
          </li>
          <li class="list_in_category_specialist d-flex flex-column align-items-stretch py-2">
            <a class="link_in_category_specialist font-weight-bold" href="" title="Экспресс-линька">Экспресс-линька кошек</a>
            <small class="text-muted">Студия «Грум-Тайм»</small>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                <span class="commercial-old-price">2 000 ₽</span>
                <span class="commercial-new-price">1 500 ₽</span>
              </div>
              <a href="" class="btn btn-sm btn-outline-primary btn-commercial">Запись</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="mobile-popular-wrapper">
  <h2 class="mobile-popular-title">Акции и скидки в г. {{ $currentCityName }}</h2>
  <div class="mobile-popular-scroll">
    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title d-flex justify-content-between align-items-center">
        <span>Ветклиники</span>
        <span class="badge-discount">-25%</span>
      </figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">Комплексная вакцинация</a>
          <span class="mobile-clinic-name">Ветклиника «Айболит»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">2 500 ₽</span>
              <span class="commercial-new-price">1 800 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Успеть</a>
          </div>
        </li>
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">УЗИ брюшной полости</a>
          <span class="mobile-clinic-name">Центр «Счастливая морда»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">1 800 ₽</span>
              <span class="commercial-new-price">1 350 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Успеть</a>
          </div>
        </li>
      </ul>
    </div>

    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title d-flex justify-content-between align-items-center">
        <span>Диагностика</span>
        <span class="badge bg-success text-white" style="font-size:0.75rem;">Выгода</span>
      </figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">Чек-ап «Здоровый котенок»</a>
          <span class="mobile-clinic-name">Ветцентр «ЗооДоктор»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">4 000 ₽</span>
              <span class="commercial-new-price">2 900 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Смотреть</a>
          </div>
        </li>
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">УЗ-чистка зубов</a>
          <span class="mobile-clinic-name">Клиника «Вега»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">3 500 ₽</span>
              <span class="commercial-new-price">2 490 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Смотреть</a>
          </div>
        </li>
      </ul>
    </div>

    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title d-flex justify-content-between align-items-center">
        <span>Груминг</span>
        <span class="badge-discount">Скидка</span>
      </figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">Комплекс для Йорка</a>
          <span class="mobile-clinic-name">Салон «Красивый Хвост»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">2 200 ₽ `</span>
              <span class="commercial-new-price">1 650 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Запись</a>
          </div>
        </li>
        <li class="mobile-popular-item">
          <a class="mobile-popular-link" href="">Экспресс-линька кошек</a>
          <span class="mobile-clinic-name">Студия «Грум-Тайм»</span>
          <div class="mobile-commercial-footer">
            <div>
              <span class="commercial-old-price">2 000 ₽</span>
              <span class="commercial-new-price">1 500 ₽</span>
            </div>
            <a href="" class="btn btn-sm btn-primary btn-commercial">Запись</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>