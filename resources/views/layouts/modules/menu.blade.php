<div class="container my-3">
    <div class="row g-3 justify-content-center">

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <!-- <a class="text-decoration-none" href="#"> -->
                <div class="div_btn_category category_disabled">
                    <div class="text_menu_btn_category">Животные</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Перейти к галереии животных" src="{{ Storage::url('icon/button/menu/pet_menu.webp') }}" alt="Животные">
                    </div>
                    <div class="will_be_soon">Скоро</div>
                </div>
            <!-- </a> -->
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('clinics.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Клиники</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Перейти к каталогу клиник" src="{{ Storage::url('icon/button/menu/clinic_menu.webp') }}" alt="Клиники">
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('doctors.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Ветеринары</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" title="Перейти к катологу ветеринаров" src="{{ Storage::url('icon/button/menu/doctor_menu.webp') }}" alt="Ветеринары">
                    </div>
                </div>
            </a>
        </div>


        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Грумеры</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" title="Перейти к каталогу Грумеров" src="{{ Storage::url('icon/button/menu/trainer_menu.webp') }}" alt="Грумеры">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Работа <br> для животных</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" title="Открыть обьявления о работе для животных" src="{{ Storage::url('icon/button/menu/petjob_menu.webp') }}" alt="Работа для животных">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Все остальное</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" title="ОТкрыть список прочего" src="{{ Storage::url('icon/button/menu/other_menu.webp') }}" alt="Все остальное">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

    </div>
</div>
