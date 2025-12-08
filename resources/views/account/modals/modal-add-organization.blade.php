@vite(['resources/js/app.js'])

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<div class="modal fade" id="addOrganizationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addOrganizationForm"
                method="POST"
                action="/add-doctor"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Добавление Организации</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="doctorErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-check-label">
                                <input type="checkbox" name="its_me" class="form-check-input">
                                <strong>Это моя организация</strong>
                                <div class="label_its_me">
                                    Мы попросим вас подтвердить что вы владелец или доверенное лицо организации
                                </div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label>Сфера деятельности</label>
                            <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
                                <option value="">Загрузка...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Название организации</label>
                            <input type="text" name="name" class="form-control">
                        </div>

                        {{-- region / city dependent selects --}}
                        <div class="col-md-6">
                            <label>Регион</label>
                            <select name="region" id="regionSelect" class="form-select">
                                <option value="">Выберите регион</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->region }}">{{ $city->region }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Город</label>
                            <select name="city_id" id="citySelect" class="form-select">
                                <option value="">Сначала выберите регион</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Улица</label>
                            <input type="text" name="street" class="form-control">
                        </div>
                        <div class="col-6">
                            <label>Дом</label>
                            <input type="text" name="street" class="form-control">
                        </div>                        
                        
<div class="col-6">
    <label>Телефон</label>
    <input type="phone" name="phone" class="form-control">
<label for="#messendger">Выберите соц сети к которым привязан этот контакт</label>
    <div id="messendger" class="d-flex gap-3 mt-2 messenger-icons">

        <!-- Telegram -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="telegram" class="d-none">
            <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" title="По этому номеру можно связатся в Телеграмм" alt="Telegram">
        </label>

        <!-- WhatsApp -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="whatsapp" class="d-none">
            <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" title="По этому номеру можно связатся в Вотсапп" alt="WhatsApp">
        </label>

        <!-- Messenger Max (VK Messenger) -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="messenger" class="d-none">
            <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" title="По этому номеру можно связатся в Max" alt="Messenger">
        </label>

    </div>
</div>

<style>
    .messenger-icons img {
        width: 36px;
        height: 36px;
        cursor: pointer;
        transition: 0.2s;
        opacity: 0.2;
    }
    .messenger-icons input:checked + img {
        opacity: 1;
        transform: scale(1.2);
    }
</style>

                            
                        <div class="col-6">
                            <label>Почта</label>
                            <input type="mail" name="street" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Логотип</label>

                            <div id="photoPicker" style="width:150px;height:150px;border:2px dashed #b8b8b8;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:48px;color:#999;">+</div>

                            <input type="file" id="doctorPhotoInput" name="photo" accept="image/*" style="display:none;">
                            <img id="doctorPhotoPreview" class="mt-2" style="width:150px;height:150px;border-radius:10px;object-fit:cover;display:none;">
                        </div>

                        <div class="col-12">
                            <label>Расскажите об организации</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Опишите род деятельности"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Сохранить</button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- Cropper modal (если используется) -->
<div id="cropper-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:#fff; padding:20px; border-radius:10px; max-width:90%; max-height:90%;">
        <img id="cropper-image" style="max-width:100%; max-height:70vh;">
        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-secondary" id="close-cropper">Отмена</button>
            <button class="btn btn-primary" id="save-cropped">Обрезать и сохранить</button>
        </div>
    </div>
</div>

<script>
/* ===============================================================
   Универсальная и полная сборка логики для модалки добавления
   - region → city (Choices.js + поиск)
   - загрузка сфер деятельности
   - загрузка клиник по городу (если в форме есть clinicSelect)
   - превью файла + кроппер (если initCropper присутствует)
   - AJAX отправка формы с обработкой ошибок
   - ограничение опыта по дате рождения (если поля есть)
   =============================================================== */

(function () {
    // Choices-инстансы
    let regionChoices = null;
    let cityChoices = null;
    let clinicChoices = null;

    function initChoicesFor(selector, opts = {}) {
        const el = document.querySelector(selector);
        if (!el) return null;
        // destroy previous if exists
        try { if (el._choicesInstance) el._choicesInstance.destroy(); } catch (e) {}
        const instance = new Choices(el, Object.assign({
            searchPlaceholderValue: "Поиск...",
            removeItemButton: true,
            shouldSort: false
        }, opts));
        el._choicesInstance = instance;
        return instance;
    }

    // Функция инициализации модального блока (вызывается при shown.bs.modal)
    function initAddOrganizationModal(modal) {
        // элементы
        const form = modal.querySelector('#addOrganizationForm');
        const fieldSelect = modal.querySelector('#fieldOfActivitySelect');
        const regionSelect = modal.querySelector('#regionSelect');
        const citySelect = modal.querySelector('#citySelect');
        const clinicSelect = modal.querySelector('#clinicSelect'); // может отсутствовать
        const errBox = modal.querySelector('#doctorErrors');

        // Инициализация Choices
        regionChoices = initChoicesFor('#regionSelect', { searchPlaceholderValue: "Поиск региона..." });
        cityChoices   = initChoicesFor('#citySelect',   { searchPlaceholderValue: "Поиск города..." });
        if (clinicSelect) clinicChoices = initChoicesFor('#clinicSelect', { searchPlaceholderValue: "Поиск клиники..." });

        // ========== загрузка сфер деятельности ==========
        if (fieldSelect) {
fetch('/api/fields/vetclinic')
                .then(r => {
                    if (!r.ok) throw new Error('Ошибка загрузки сфер');
                    return r.json();
                })
                .then(list => {
                    fieldSelect.innerHTML = `<option value="">Выберите сферу деятельности</option>`;
                    list.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        fieldSelect.appendChild(opt);
                    });
                })
                .catch(err => {
                    console.error(err);
                    fieldSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                });
        }

        // ========== region -> city ==========
        if (regionSelect && citySelect) {
            regionSelect.addEventListener('change', function () {
                const region = this.value;

                // сброс city
                if (citySelect._choicesInstance) {
                    try { citySelect._choicesInstance.clearStore(); } catch(e) {}
                }
                citySelect.innerHTML = `<option value="">Загрузка...</option>`;
                if (citySelect._choicesInstance) citySelect._choicesInstance.setChoices([{value:'',label:'Загрузка...'}], 'value', 'label', true);

                if (!region) {
                    citySelect.innerHTML = `<option value="">Сначала выберите регион</option>`;
                    if (citySelect._choicesInstance) {
                        citySelect._choicesInstance.setChoices([{value:'',label:'Сначала выберите регион'}], 'value', 'label', true);
                    }
                    return;
                }

                const url = `/api/cities/by-region/${encodeURIComponent(region)}`;

                fetch(url)
                    .then(r => {
                        if (!r.ok) throw new Error('Network error');
                        return r.json();
                    })
                    .then(list => {
                        // ожидаем list = [{id, name}, ...]
                        if (!Array.isArray(list) || list.length === 0) {
                            citySelect.innerHTML = `<option value="">Города не найдены</option>`;
                            if (citySelect._choicesInstance) citySelect._choicesInstance.setChoices([{value:'',label:'Города не найдены'}], 'value', 'label', true);
                            return;
                        }

                        // создаём опции
                        citySelect.innerHTML = `<option value="">Выберите город</option>`;
                        list.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.name;
                            citySelect.appendChild(opt);
                        });

                        // пересоздаём Choices
                        if (citySelect._choicesInstance) {
                            // убрать старое и создать заново чтобы обновились опции
                            citySelect._choicesInstance.setChoices(
                                Array.from(citySelect.options).map(o => ({value: o.value, label: o.text})),
                                'value', 'label', true
                            );
                        }
                    })
                    .catch(err => {
                        console.error('Ошибка загрузки городов:', err);
                        citySelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                        if (citySelect._choicesInstance) citySelect._choicesInstance.setChoices([{value:'',label:'Ошибка загрузки'}], 'value', 'label', true);
                    });
            });
        }

        // ========== Подгрузка клиник по выбранному городу (если есть в форме) ==========
        if (citySelect && clinicSelect) {
            citySelect.addEventListener('change', function () {
                const cityId = this.value;
                clinicSelect.innerHTML = `<option value="">Загрузка...</option>`;
                if (clinicSelect._choicesInstance) clinicSelect._choicesInstance.setChoices([{value:'',label:'Загрузка...'}], 'value', 'label', true);

                if (!cityId) {
                    clinicSelect.innerHTML = `<option value="">Сначала выберите город</option>`;
                    if (clinicSelect._choicesInstance) clinicSelect._choicesInstance.setChoices([{value:'',label:'Сначала выберите город'}], 'value', 'label', true);
                    return;
                }

                fetch(`/api/clinics/by-city/${encodeURIComponent(cityId)}`)
                    .then(r => {
                        if (!r.ok) throw new Error('Network error');
                        return r.json();
                    })
                    .then(list => {
                        clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;
                        list.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.name;
                            clinicSelect.appendChild(opt);
                        });
                        if (clinicSelect._choicesInstance) {
                            clinicSelect._choicesInstance.setChoices(
                                Array.from(clinicSelect.options).map(o => ({value: o.value, label: o.text})),
                                'value', 'label', true
                            );
                        }
                    })
                    .catch(err => {
                        console.error('Ошибка загрузки клиник:', err);
                        clinicSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                        if (clinicSelect._choicesInstance) clinicSelect._choicesInstance.setChoices([{value:'',label:'Ошибка загрузки'}], 'value', 'label', true);
                    });
            });
        }

        // ========== Превью и кроппер для логотипа ==========
        const fileInput = modal.querySelector("#doctorPhotoInput");
        const preview = modal.querySelector("#doctorPhotoPreview");
        const picker = modal.querySelector("#photoPicker");

        if (fileInput && preview && picker) {
            // показываем/скрываем превью
            function showPreview(file) {
                if (!file) return;
                try {
                    const url = URL.createObjectURL(file);
                    preview.src = url;
                    preview.style.display = "block";
                    picker.style.display = "none";
                } catch (err) { console.error(err); }
            }

            // fallback без кроппера
            function fallbackAttach() {
                fileInput.addEventListener('change', () => {
                    const f = fileInput.files && fileInput.files[0];
                    if (!f) {
                        preview.style.display = 'none';
                        picker.style.display = 'flex';
                        return;
                    }
                    showPreview(f);
                });
            }

            // клики
            picker.addEventListener('click', () => fileInput.click());
            preview.addEventListener('click', () => fileInput.click());

            // если глобально присутствует initCropper (внешний файл cropper-init.js), используем его
            try {
                if (typeof initCropper === 'function') {
                    initCropper(fileInput, preview); // предполагается, что initCropper сгенерирует кроппер и сам сохранит файл в input
                    fileInput.addEventListener('change', () => {
                        const f = fileInput.files && fileInput.files[0];
                        if (f) showPreview(f);
                        else { preview.style.display = 'none'; picker.style.display = 'flex'; }
                    });
                } else {
                    fallbackAttach();
                }
            } catch (err) {
                console.error('Ошибка инициализации cropper:', err);
                fallbackAttach();
            }

            preview.addEventListener('dblclick', () => {
                preview.src = '';
                preview.style.display = 'none';
                picker.style.display = 'flex';
                fileInput.value = '';
            });
        }

        // ========== Ограничение опыта по дате рождения (если поля присутствуют) ==========
        const dobInput = modal.querySelector('#date_of_birth');
        const expInput = modal.querySelector('#experience');
        if (dobInput && expInput) {
            dobInput.addEventListener('change', function () {
                const dob = new Date(this.value);
                if (isNaN(dob)) return;
                const now = new Date();
                let age = now.getFullYear() - dob.getFullYear();
                if (now.getMonth() < dob.getMonth() || (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate())) {
                    age--;
                }
                const maxExperience = Math.max(0, age - 18);
                expInput.max = maxExperience;
                if (parseInt(expInput.value || 0) > maxExperience) expInput.value = maxExperience;
            });
        }

        // ========== AJAX отправка формы ==========
        if (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (!errBox) return;

                errBox.classList.add('d-none');
                errBox.innerHTML = '';

                const data = new FormData(form);

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {'Accept': 'application/json'},
                        body: data
                    });

                    const json = await res.json().catch(()=>({}));

                    if (!res.ok) {
                        // Если Laravel вернул валидационные ошибки (422)
                        if (res.status === 422 && json.errors) {
                            let html = '';
                            Object.keys(json.errors).forEach(name => {
                                html += `<div>${json.errors[name][0]}</div>`;
                                const input = form.querySelector(`[name="${name}"]`);
                                if (input) input.classList.add('is-invalid');
                            });
                            errBox.innerHTML = html;
                            errBox.classList.remove('d-none');
                            errBox.scrollIntoView({behavior:'smooth'});
                            return;
                        }
                        // прочие ошибки
                        const msg = (json && json.message) ? json.message : 'Ошибка сервера';
                        errBox.innerHTML = `<div>${msg}</div>`;
                        errBox.classList.remove('d-none');
                        return;
                    }

                    // успешный ответ
                    const message = (json && json.message) ? json.message : 'Сохранено';
                    alert(message);
                    // закрыть модал или перезагрузить страницу
                    location.reload();
                } catch (err) {
                    console.error('Ошибка отправки формы:', err);
                    errBox.innerHTML = `<div>Ошибка отправки: ${err.message}</div>`;
                    errBox.classList.remove('d-none');
                }
            });
        }
    }

    // Запуск инициализации при открытии модалки
    document.addEventListener('shown.bs.modal', function (event) {
        const modal = event.target;
        if (modal && modal.id === 'addOrganizationModal') {
            initAddOrganizationModal(modal);
        }
    });

    // Если модалка уже открыта (при загрузке страницы), можно инициализировать сразу
    const already = document.querySelector('#addOrganizationModal');
    if (already && already.classList.contains('show')) {
        initAddOrganizationModal(already);
    }

})();
</script>
