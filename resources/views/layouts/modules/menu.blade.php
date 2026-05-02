<div class="container my-3">
    <div class="row g-3 justify-content-center">

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('animals.index') }}">
                <div class="div_btn_category category">
                    <div class="text_menu_btn_category">Животные</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Галерея животных" src="{{ Storage::url('icon/button/menu/pet_menu.webp') }}" alt="Животные">
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('clinics.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Клиники</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Каталогу клиник" src="{{ Storage::url('icon/button/menu/clinic_menu.webp') }}" alt="Клиники">
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('doctors.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Ветеринары</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Катологу ветеринаров" src="{{ Storage::url('icon/button/menu/doctor_menu.webp') }}" alt="Ветеринары">
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('specialists.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category ">Специалисты</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Каталог специалистов" src="{{ Storage::url('icon/button/menu/specialist_menu.webp') }}" alt="Иконка специалисты">
                    </div>
                    
                </div>
            </a>
        </div>
        
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
               <a class="text-decoration-none" href="{{ route('organizations.index') }}">
            <div class="div_btn_category ">
                <div class="text_menu_btn_category">Организации</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" title="Каталог зоомагазинов, ветеринарных аптек, зоогостиниц, кинологических центров" src="{{ Storage::url('icon/button/menu/organizations_menu.webp') }}" alt="Иконка раздела организации ">
                </div>
                <!-- <div class="will_be_soon">Скоро</div> -->
            </div>
                        </a>
        </div>
        
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
         <a class="nav-link {{ request()->routeIs('ads.*') ? 'active' : '' }}" href="{{ route('ads.index') }}">
            <div class="div_btn_category">
                <div class="text_menu_btn_category">Доска <br> Объявлений</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" title="Доска объявлений для обмена, продажи атрибутов для животных" src="{{ Storage::url('icon/button/menu/ads_menu.webp') }}" alt="Иконка объявлений">
                </div>
               
            </div>
            </a>
        </div>

    </div>
</div>
