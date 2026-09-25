{{--
    Глобальное модальное окно просмотра фото.

    Как пользоваться на странице:
    1. Оберни ссылку и подпись общим контейнером с атрибутом data-zoom-scope.
    2. Ссылка на увеличенное фото — <a class="js-zoom" href="{url}" data-title="...">.
    3. Внутри того же scope помести элемент с data-zoom-info — именно его
       содержимое (клонированное) станет подписью под фото в модалке.

    Подключается один раз в layouts/footer.blade.php и работает на любой
    странице, где встречается такая разметка (аватар, питомцы, отзывы и т.д.).
--}}
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imageZoomImg" src="" alt="" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
                {{-- Сюда копируется подпись с миниатюры (имя, возраст, дата смерти и т.д.) --}}
                <div id="imageZoomCaption" class="mt-3 text-white text-center"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Умершие питомцы: чёрно-белое фото, при наведении — цветное */
    .pet-deceased { filter: grayscale(1); transition: filter .3s ease; }
    a.js-zoom:hover .pet-deceased { filter: none; }

    #imageZoomCaption .text-muted { color: #c9d1d9 !important; }
    #imageZoomCaption .bi { color: #1ccfc9; }
</style>

<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('a.js-zoom');
    if (!link) return;

    const modalEl = document.getElementById('imageZoomModal');
    // Если Bootstrap по какой-то причине не загрузился — ссылка откроет фото в этой же вкладке
    if (!modalEl || !window.bootstrap?.Modal) return;

    e.preventDefault();
    document.getElementById('imageZoomImg').src = link.getAttribute('href');
    document.getElementById('imageZoomImg').alt = link.dataset.title || '';

    // Подпись под фото = ровно то, что написано рядом с миниатюрой
    const caption = document.getElementById('imageZoomCaption');
    caption.innerHTML = '';
    const info = link.closest('[data-zoom-scope]')?.querySelector('[data-zoom-info]');
    if (info) {
        const clone = info.cloneNode(true);
        clone.className = '';
        clone.removeAttribute('data-zoom-info');
        // Сбрасываем инлайн display:none (если блок на странице был спрятан им)
        clone.style.display = '';
        // В модалке не должно быть второго <h1> на странице
        clone.querySelectorAll('h1').forEach(function (h) {
            const d = document.createElement('div');
            d.className = 'h4 fw-bold mb-2';
            d.innerHTML = h.innerHTML;
            h.replaceWith(d);
        });
        caption.appendChild(clone);
    }
    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
});
</script>
