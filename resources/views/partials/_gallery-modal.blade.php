{{-- Единая модалка-галерея для организаций/клиник/врачей/специалистов.
     Открывается любым элементом с классом .js-gallery-cover и атрибутом
     data-photos='["url1","url2",...]' — см. resources/js/gallery.js --}}
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index:3;" data-bs-dismiss="modal" aria-label="Закрыть"></button>

            <div class="modal-body p-0 d-flex align-items-center justify-content-center position-relative">
                <button type="button" class="gallery-nav gallery-nav--prev" id="galleryPrevBtn" aria-label="Предыдущее фото">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <img id="galleryModalImg" src="" alt="" style="max-height:90vh; max-width:90vw; width:auto; object-fit:contain;">

                <button type="button" class="gallery-nav gallery-nav--next" id="galleryNextBtn" aria-label="Следующее фото">
                    <i class="bi bi-chevron-right"></i>
                </button>

                <div class="gallery-counter" id="galleryCounter"></div>
            </div>
        </div>
    </div>
</div>
