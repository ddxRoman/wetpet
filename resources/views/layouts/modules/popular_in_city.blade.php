<style>
/* === ДЕСКТОП — как было === */
@media (min-width: 993px) {
  .mobile-popular-wrapper {
    display: none !important;
  }
}

/* === МОБИЛЬНАЯ ВЕРСИЯ — отдельные классы === */
@media (max-width: 992px) {
  .desktop-popular-wrapper {
    display: none !important;
  }

  .mobile-popular-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 15px;
  }

  .mobile-popular-title {
    font-size: 1.4rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 15px;
  }

  .mobile-popular-scroll {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    gap: 15px;
    padding: 10px 0;
  }

  .mobile-popular-card {
    flex: 0 0 85%;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    padding: 15px;
    scroll-snap-align: center;
  }

  .mobile-popular-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 10px;
    color: #333;
  }

  .mobile-popular-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .mobile-popular-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 10px;
    background: #f8f8f8;
    border-radius: 8px;
    margin-bottom: 6px;
  }

  .mobile-popular-link {
    text-decoration: none;
    color: #007bff;
    font-size: 0.95rem;
    font-weight: 500;
  }

  .mobile-popular-count {
    font-size: 0.9rem;
    color: #666;
  }
}
</style>

<!-- === ДЕСКТОПНАЯ ВЕРСИЯ (остается без изменений) === -->
<div class="container desktop-popular-wrapper">
  <div class="row">
    <h2 class="header_h2">Популярное в городе {{ $currentCityName }}</h2>

    <div class="col-4">
      <div class="most_popular_in_city border">
        <figcaption class="title_list_popular">Врачи</figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Специализация</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Специализация</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Специализация</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Специализация</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Специализация</a><span class="list_specialist_count">50</span></li>
        </ul>
      </div>
    </div>

    <div class="col-4">
      <div class="most_popular_in_city border">
        <figcaption class="title_list_popular">Вет центры</figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Клиника</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Клиника</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Клиника</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Клиника</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Клиника</a><span class="list_specialist_count">50</span></li>
        </ul>
      </div>
    </div>

    <div class="col-4">
      <div class="most_popular_in_city border">
        <figcaption class="title_list_popular">Зоомагазины</figcaption>
        <ul class="list_doctor">
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Корм</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Корм</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Корм</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Корм</a><span class="list_specialist_count">50</span></li>
          <li class="list_in_category_specialist"><a class="link_in_category_specialist" href="" title="Специализация">Корм</a><span class="list_specialist_count">50</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- === МОБИЛЬНАЯ ВЕРСИЯ (отдельные классы, без конфликтов) === -->
<div class="mobile-popular-wrapper">
  <h2 class="mobile-popular-title">Популярное в городе {{ $currentCityName }}</h2>

  <div class="mobile-popular-scroll">
    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title">Врачи</figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Специализация</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Специализация</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Специализация</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Специализация</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Специализация</a><span class="mobile-popular-count">50</span></li>
      </ul>
    </div>

    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title">Вет центры</figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Клиника</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Клиника</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Клиника</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Клиника</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Клиника</a><span class="mobile-popular-count">50</span></li>
      </ul>
    </div>

    <div class="mobile-popular-card">
      <figcaption class="mobile-popular-card-title">Зоомагазины</figcaption>
      <ul class="mobile-popular-list">
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Корм</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Корм</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Корм</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Корм</a><span class="mobile-popular-count">50</span></li>
        <li class="mobile-popular-item"><a class="mobile-popular-link" href="" title="Специализация">Корм</a><span class="mobile-popular-count">50</span></li>
      </ul>
    </div>
  </div>
</div>
