@vite(['resources/js/app.js'])

@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="modal fade" id="addOrganizationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">

            {{-- Заголовок --}}
            <div class="modal-header px-4 py-3 border-0 flex-shrink-0"
                 style="background:linear-gradient(135deg,#1ccfc9 0%,#0fa8c0 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:42px;height:42px;background:rgba(255,255,255,0.2);font-size:20px;">🏢</div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size:17px;">Добавление организации</h5>
                        <div class="text-white" style="font-size:12px;opacity:.8;">Заполните информацию об организации</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
            </div>

            {{-- Тело (скроллится) --}}
            <div class="modal-body px-4 py-4">

                <div id="orgErrors" class="alert alert-danger rounded-3 d-none" style="font-size:14px;"></div>

                <form id="addOrganizationForm"
                      method="POST"
                      action="/add-organization"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Это моя организация --}}
                    <div class="p-3 rounded-3 mb-4 d-flex align-items-start gap-3"
                         style="background:#eef3ff;border:1px solid #b2dff0;">
                        <div class="pt-1">
                            <input type="checkbox" name="its_me" class="form-check-input" id="orgItsMe"
                                   style="width:18px;height:18px;cursor:pointer;accent-color:#1ccfc9;">
                        </div>
                        <label for="orgItsMe" style="cursor:pointer;">
                            <div class="fw-semibold" style="font-size:14px;color:#0a6e7a;">Это моя организация</div>
                            <div class="text-muted" style="font-size:12px;margin-top:2px;">
                                Мы попросим вас подтвердить, что вы владелец или доверенное лицо организации.
                            </div>
                        </label>
                    </div>

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Сфера деятельности</label>
                            <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select wpm-input">
                                <option value="">Загрузка...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Название организации</label>
                            <input type="text" name="name" class="form-control wpm-input"
                                   placeholder="Например: Ветеринарная клиника «Лапа»">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Регион</label>
                            <select name="region" id="regionSelect" class="form-select wpm-input">
                                <option value="">Выберите регион</option>
                                @foreach($cities as $city)
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

                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Улица</label>
                            <input type="text" name="street" class="form-control wpm-input" placeholder="ул. Мира">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Дом</label>
                            <input type="text" name="house" class="form-control wpm-input" placeholder="10/1">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Телефоны</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="tel" name="phone1" class="form-control wpm-input"
                                           placeholder="+7 (___) ___-__-__">
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <span style="font-size:12px;color:#6b7280;">Мессенджеры:</span>
                                        <label style="cursor:pointer;" title="Telegram">
                                            <input type="checkbox" name="messengers[]" value="telegram" class="d-none">
                                            <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" alt="Telegram"
                                                 style="width:28px;height:28px;border-radius:6px;opacity:.6;transition:opacity .2s;"
                                                 onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=.6">
                                        </label>
                                        <label style="cursor:pointer;" title="Messenger Max">
                                            <input type="checkbox" name="messengers[]" value="messenger" class="d-none">
                                            <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" alt="Max"
                                                 style="width:28px;height:28px;border-radius:6px;opacity:.6;transition:opacity .2s;"
                                                 onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=.6">
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" name="phone2" class="form-control wpm-input"
                                           placeholder="+7 (___) ___-__-__ (доп.)">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Email</label>
                            <input type="email" name="email" class="form-control wpm-input" placeholder="info@example.ru">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Часы работы</label>
                            <input type="text" name="schedule" class="form-control wpm-input" placeholder="09:00–20:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Рабочие дни</label>
                            <input type="text" name="workdays" class="form-control wpm-input" placeholder="Пн–Пт">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-block" style="font-size:13px;color:#374151;">Логотип</label>
                            <input type="file" id="orgPhotoInput" name="logo" accept="image/*" class="d-none">
                            <div id="orgPhotoPicker"
                                 style="cursor:pointer;width:90px;height:90px;border:2px dashed #b2dff0;
                                        border-radius:12px;display:flex;flex-direction:column;align-items:center;
                                        justify-content:center;background:#eef3ff;transition:all .2s;"
                                 onmouseenter="this.style.borderColor='#1ccfc9';this.style.background='#d9f5fb'"
                                 onmouseleave="this.style.borderColor='#b2dff0';this.style.background='#eef3ff'">
                                <span style="font-size:26px;line-height:1;color:#1ccfc9;">＋</span>
                                <span style="font-size:10px;color:#6b7280;margin-top:4px;">Логотип</span>
                            </div>
                            <div id="orgPhotoPreviewWrapper" style="display:none;position:relative;width:90px;">
                                <img id="orgPhotoPreview" style="width:90px;height:90px;object-fit:cover;border-radius:12px;display:block;">
                                <button type="button" id="orgRemovePhotoBtn"
                                        style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;
                                               border-radius:50%;border:none;background:#dc3545;color:#fff;
                                               font-size:14px;cursor:pointer;line-height:1;">&times;</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Об организации</label>
                            <textarea name="description" rows="4" class="form-control wpm-input"
                                      placeholder="Опишите род деятельности, направления, особенности"></textarea>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Футер --}}
            <div class="modal-footer px-4 py-3 border-0 flex-shrink-0" style="background:#f0f8ff;">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"
                        style="font-size:14px;border:1px solid #dee2e6;">Отмена</button>
                <button type="button" id="orgSubmitBtn" class="btn rounded-pill px-5 fw-semibold text-white"
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
    // Логотип
    const picker  = document.getElementById('orgPhotoPicker');
    const input   = document.getElementById('orgPhotoInput');
    const preview = document.getElementById('orgPhotoPreview');
    const wrapper = document.getElementById('orgPhotoPreviewWrapper');
    const rmBtn   = document.getElementById('orgRemovePhotoBtn');

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

    // Кнопка Сохранить вне формы — сабмитим форму вручную
    document.getElementById('orgSubmitBtn')?.addEventListener('click', function () {
        document.getElementById('addOrganizationForm')?.requestSubmit();
    });

    // Форма
    const form       = document.getElementById('addOrganizationForm');
    const errorBlock = document.getElementById('orgErrors');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorBlock.classList.add('d-none');
            errorBlock.innerHTML = '';

            const formData       = new FormData(form);
            const isItsMeChecked = form.querySelector('input[name="its_me"]').checked;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok) {
                    window.location.href = isItsMeChecked
                        ? "{{ route('owner.index') }}"
                        : window.location.href;
                    window.location.reload();
                } else {
                    errorBlock.classList.remove('d-none');
                    if (data.errors) {
                        let html = '<ul class="mb-0">';
                        Object.values(data.errors).forEach(arr => arr.forEach(m => { html += `<li>${m}</li>`; }));
                        html += '</ul>';
                        errorBlock.innerHTML = html;
                    } else {
                        errorBlock.innerText = data.message || 'Произошла ошибка при сохранении.';
                    }
                    errorBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            })
            .catch(() => {
                errorBlock.classList.remove('d-none');
                errorBlock.innerText = 'Системная ошибка при отправке формы.';
            });
        });
    }
});
</script>