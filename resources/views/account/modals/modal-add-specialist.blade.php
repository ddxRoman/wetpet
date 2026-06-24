<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">

            {{-- Заголовок --}}
            <div class="modal-header px-4 py-3 border-0 flex-shrink-0"
                 style="background:linear-gradient(135deg,#1ccfc9 0%,#0fa8c0 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:42px;height:42px;background:rgba(255,255,255,0.2);font-size:20px;">🩺</div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size:17px;">Добавление специалиста</h5>
                        <div class="text-white" style="font-size:12px;opacity:.8;">Заполните информацию о специалисте</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
            </div>

            {{-- Тело (скроллится) --}}
            <div class="modal-body px-4 py-4">

                <div id="doctorErrors" class="alert alert-danger rounded-3 d-none" style="font-size:14px;"></div>

                <form id="addDoctorForm"
                      method="POST"
                      action="{{ route('specialist.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    @if(auth()->check() && auth()->user()->canAddSelfSpecialist())
                    <div class="p-3 rounded-3 mb-4 d-flex align-items-start gap-3"
                         style="background:#eef3ff;border:1px solid #b2dff0;">
                        <div class="pt-1">
                            <input type="checkbox" name="its_me" class="form-check-input" id="itsMe"
                                   style="width:18px;height:18px;cursor:pointer;accent-color:#1ccfc9;">
                        </div>
                        <label for="itsMe" style="cursor:pointer;">
                            <div class="fw-semibold" style="font-size:14px;color:#0a6e7a;">Добавляю себя</div>
                            <div class="text-muted" style="font-size:12px;margin-top:2px;">
                                Мы попросим подтвердить, что это вы.
                            </div>
                        </label>
                    </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Сфера деятельности</label>
                            <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select wpm-input">
                                <option value="">Загрузка...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Имя специалиста</label>
                            <input type="text" name="name" class="form-control wpm-input"
                                   placeholder="Например: Иванов Иван Иванович">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Дата рождения</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control wpm-input"
                                   max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Стаж (лет)</label>
                            <input type="number" id="experience" name="experience"
                                   class="form-control wpm-input" min="0" placeholder="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Регион</label>
                            <select name="region" id="regionSelect" class="form-select wpm-input">
                                <option value="">Выберите регион</option>
                                @foreach($cities->unique('region') as $city)
                                    <option value="{{ $city->region }}">{{ $city->region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Город</label>
                            <select name="city_id" id="citySelect" class="form-select wpm-input">
                                <option value="">Сначала выберите регион</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Организация</label>
                            <select name="clinic_id" id="clinicSelect" class="form-select wpm-input">
                                <option value="">Сначала выберите город</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="p-3 rounded-3" style="background:#f0f8ff;border:1px solid #b2dff0;">
                                <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                                    <input class="form-check-input flex-shrink-0" type="checkbox"
                                           id="is_private" style="width:42px;height:22px;cursor:pointer;accent-color:#1ccfc9;">
                                    <label class="form-check-label m-0" for="is_private"
                                           style="font-size:13px;color:#374151;cursor:pointer;">
                                        Я частный специалист (работаю без привязки к клинике/центру)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="address-section-add" style="display:none;" class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Улица</label>
                                    <input type="text" name="street" id="street" class="form-control wpm-input" placeholder="ул. Мира">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Дом</label>
                                    <input type="text" name="house" id="house" class="form-control wpm-input" placeholder="10/1">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Телефон</label>
                            <input type="tel" name="phone" class="form-control wpm-input" placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Email</label>
                            <input type="email" name="mail" class="form-control wpm-input" placeholder="example@mail.ru">
                        </div>

                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 h-100" style="background:#f0f8ff;border:1px solid #b2dff0;">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                                            <input class="form-check-input flex-shrink-0" type="checkbox"
                                                   name="exotic_animals" id="exotic_animals" value="Да"
                                                   style="width:42px;height:22px;cursor:pointer;accent-color:#1ccfc9;">
                                            <label class="form-check-label m-0" for="exotic_animals"
                                                   style="font-size:13px;color:#374151;cursor:pointer;">
                                                🦎 Работаю с экзотическими животными
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 h-100" style="background:#f0f8ff;border:1px solid #b2dff0;">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                                            <input class="form-check-input flex-shrink-0" type="checkbox"
                                                   name="On_site_assistance" id="On_site_assistance" value="Да"
                                                   style="width:42px;height:22px;cursor:pointer;accent-color:#1ccfc9;">
                                            <label class="form-check-label m-0" for="On_site_assistance"
                                                   style="font-size:13px;color:#374151;cursor:pointer;">
                                                🚗 Выезд на дом
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-block" style="font-size:13px;color:#374151;">Фото специалиста</label>
                            <input type="file" name="photo" id="doctorPhotoInput" class="d-none" accept="image/*">
                            <div id="doctorPhotoPicker"
                                 style="cursor:pointer;width:90px;height:90px;border:2px dashed #b2dff0;
                                        border-radius:12px;display:flex;flex-direction:column;align-items:center;
                                        justify-content:center;background:#eef3ff;transition:all .2s;"
                                 onmouseenter="this.style.borderColor='#1ccfc9';this.style.background='#d9f5fb'"
                                 onmouseleave="this.style.borderColor='#b2dff0';this.style.background='#eef3ff'">
                                <span style="font-size:26px;line-height:1;color:#1ccfc9;">＋</span>
                                <span style="font-size:10px;color:#6b7280;margin-top:4px;">Загрузить</span>
                            </div>
                            <div id="photoPreviewWrapper" style="display:none;position:relative;width:90px;">
                                <img id="doctorPhotoPreview"
                                     style="width:90px;height:90px;object-fit:cover;border-radius:12px;display:block;">
                                <button type="button" id="removePhotoBtn"
                                        style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;
                                               border-radius:50%;border:none;background:#dc3545;color:#fff;
                                               font-size:14px;cursor:pointer;line-height:1;">&times;</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">О специалисте</label>
                            <textarea name="description" rows="4" class="form-control wpm-input"
                                      placeholder="Опишите специализацию, направления и опыт работы"></textarea>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Футер --}}
            <div class="modal-footer px-4 py-3 border-0 flex-shrink-0" style="background:#f0f8ff;">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"
                        style="font-size:14px;border:1px solid #dee2e6;">Отмена</button>
                <button type="button" id="doctorSubmitBtn" class="btn rounded-pill px-5 fw-semibold text-white"
                        style="font-size:14px;background:#1ccfc9;border:none;transition:background .2s;"
                        onmouseenter="this.style.background='#0fa8c0'"
                        onmouseleave="this.style.background='#1ccfc9'">
                    Сохранить
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.wpm-input {
    border-radius: 10px !important;
    border: 1.5px solid #dde3f0 !important;
    padding: 9px 14px !important;
    font-size: 14px !important;
    color: #1f2937 !important;
    transition: border-color .2s, box-shadow .2s !important;
}
.wpm-input:focus {
    border-color: #1ccfc9 !important;
    box-shadow: 0 0 0 3px rgba(28,207,201,.15) !important;
    outline: none !important;
}
.wpm-input::placeholder { color: #9ca3af !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Фото
    const picker  = document.getElementById('doctorPhotoPicker');
    const input   = document.getElementById('doctorPhotoInput');
    const preview = document.getElementById('doctorPhotoPreview');
    const wrapper = document.getElementById('photoPreviewWrapper');
    const rmBtn   = document.getElementById('removePhotoBtn');

    picker?.addEventListener('click', () => input.click());
    input?.addEventListener('change', function () {
        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            picker.style.display  = 'none';
            wrapper.style.display = 'block';
        }
    });
    rmBtn?.addEventListener('click', function () {
        input.value   = '';
        preview.src   = '';
        wrapper.style.display = 'none';
        picker.style.display  = 'flex';
    });

    // Кнопка Сохранить вне формы
    document.getElementById('doctorSubmitBtn')?.addEventListener('click', function () {
        document.getElementById('addDoctorForm')?.requestSubmit();
    });

    // Форма
    const form       = document.getElementById('addDoctorForm');
    const errorBlock = document.getElementById('doctorErrors');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorBlock.classList.add('d-none');
            errorBlock.innerHTML = '';

            const formData      = new FormData(form);
            const itsMeCheckbox = form.querySelector('input[name="its_me"]');

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(async response => {
                if (response.ok) {
                    if (itsMeCheckbox && itsMeCheckbox.checked) {
                        window.location.href = "/owner";
                    } else {
                        window.location.reload();
                    }
                } else {
                    const data = await response.json();
                    if (data.errors) {
                        let html = '<ul class="mb-0">';
                        Object.values(data.errors).forEach(arr => arr.forEach(m => { html += `<li>${m}</li>`; }));
                        html += '</ul>';
                        errorBlock.innerHTML = html;
                    } else {
                        errorBlock.innerText = data.message || 'Ошибка валидации.';
                    }
                    errorBlock.classList.remove('d-none');
                    errorBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            })
            .catch(() => {
                errorBlock.classList.remove('d-none');
                errorBlock.innerText = 'Системная ошибка при отправке данных.';
            });
        });
    }
});
</script>