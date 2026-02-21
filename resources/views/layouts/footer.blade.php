@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
<footer class="footer py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <h2>{{ $brandname }}</h2>
                                <img src="{{ Storage::url('logo/logo_black-white.png') }}" title="Логотип зверозор" alt="Логотип">
                <p class="mt-3 text-muted">Ваш гид в мире заботы о питомцах.</p>
            </div>
            <div class="col-md-3">
                            
                <ul class="footer_menu">
    <li><a title="Животные" href="#">Животные</a></li>
    <li><a title="Ветеринары" href="#">Ветеринары</a></li>
    <li><a title="Ветеринарные клиники" href="#">Ветеринарные клиники</a></li>
    <li><a title="Грумеры" href="#">Грумеры</a></li>
    <li><a title="Зоомагазины" href="#">Зоомагазины</a></li>
    <li><a title="Прочие категории" href="#">Прочие категории</a></li>
    <li><a title="Прочие категории" href="legal/about">О нас</a></li>
</ul>
            </div>
            <div class="col-md-3">

<a href="legal/faq"><p>FAQ</p></a>
<a href="legal/privacy"><p>Политика конфиденциальности</p></a>
<a href="legal/terms"><p>Пользовательское соглашение</p></a>
<a href="legal/glossary"><p>Словарь</p></a>
<a href="legal/news"><p>Новости</p></a>
            </div>
            <div class="col-md-3">
                <a href="mailto:admin@zverozor.ru"><p>Связаться с нами</p></a>
                <!-- <h5>Мы в соцсетях</h5>
                <div class="d-flex gap-2">
                    <a href="#"><img src="/icon/tg.svg" width="24"></a>
                    <a href="#"><img src="/icon/wa.svg" width="24"></a>
                </div> -->
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center text-muted">
            <small>&copy; 2025 Zverozor. Все права защищены. <a href="/privacy" class="text-muted">Политика конфиденциальности</a></small>
        </div>
    </div>
</footer>