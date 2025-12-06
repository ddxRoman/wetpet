<style>
.div_btn_category {
    text-align: center;
    padding: 10px;
    border-radius: 15px;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.div_btn_category:hover {
    transform: scale(1.05);
}
.text_menu_btn_category {
    font-size: 1rem;
    font-weight: 500;
    color: #333;
}
.category_icon {
    height: 80px;
    object-fit: contain;
}
.div_btn_category_icon {
    margin-top: 5px;
}
@media (max-width: 576px) {
    .text_menu_btn_category {
        font-size: 0.9rem;
    }
    .category_icon {
        width: 50px;
        height: 50px;
    }
}
.will_be_soon{
    color: red;
    font-weight: 900;
    font-size: 24pt;
}

.category_disabled {
    pointer-events: none; /* отключает клики */
    filter: grayscale(100%); /* делает черно-белым */
    opacity: 0.6; /* легкое затемнение */
}
.category_disabled:hover {
    transform: none !important; /* убираем увеличение при наведении */
}


</style>

<div class="container my-3">
    <div class="row g-3 justify-content-center">

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <!-- <a class="text-decoration-none" href="#"> -->
                <div class="div_btn_category category_disabled">
                    <div class="text_menu_btn_category">Животные</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" src="{{ Storage::url('icon/button/menu/pet_menu.webp') }}" alt="Животные">
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
                        <img class="category_icon" src="{{ Storage::url('icon/button/menu/clinic_menu.webp') }}" alt="Клиники">
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('doctors.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Ветеринары</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" src="{{ Storage::url('icon/button/menu/doctor_menu.webp') }}" alt="Ветеринары">
                    </div>
                </div>
            </a>
        </div>


        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Дрессировщики</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/button/menu/trainer_menu.webp') }}" alt="Дрессировщики">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Работа <br> для животных</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/button/menu/petjob_menu.webp') }}" alt="Работа для животных">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category category_disabled">
                <div class="text_menu_btn_category ">Все остальное</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/button/menu/other_menu.webp') }}" alt="Все остальное">
                </div>
                <div class="will_be_soon">Скоро</div>
            </div>
        </div>

    </div>
</div>
