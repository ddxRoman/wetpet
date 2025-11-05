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
    width: 60px;
    height: 60px;
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
</style>

<div class="container my-3">
    <div class="row g-3 justify-content-center">

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="#">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Животные</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" src="{{ Storage::url('icon/button/menu/pet_menu.webp') }}" alt="Животные">
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <a class="text-decoration-none" href="{{ route('pages.clinics.index') }}">
                <div class="div_btn_category">
                    <div class="text_menu_btn_category">Клиники</div>
                    <div class="div_btn_category_icon">
                        <img class="category_icon" src="{{ Storage::url('icon/button/menu/clinic_menu.webp') }}" alt="Клиники">
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category">
                <div class="text_menu_btn_category">Зооцентры</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/category/zoocentr_icon_category.png') }}" alt="Зооцентры">
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category">
                <div class="text_menu_btn_category">Дрессировщики</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/category/trainer_icon_category.png') }}" alt="Дрессировщики">
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category">
                <div class="text_menu_btn_category">Работа <br> для животных</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/category/petjob_icon_category.png') }}" alt="Работа для животных">
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="div_btn_category">
                <div class="text_menu_btn_category">Все остальное</div>
                <div class="div_btn_category_icon">
                    <img class="category_icon" src="{{ Storage::url('icon/category/other_icon_category.png') }}" alt="Все остальное">
                </div>
            </div>
        </div>

    </div>
</div>
