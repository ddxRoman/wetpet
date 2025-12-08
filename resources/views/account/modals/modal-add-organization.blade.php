@vite(['resources/js/app.js'])

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<div class="modal fade" id="addOrganizationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addDoctorForm"
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
                                <strong>
                                    Это моя орагнизация
                                </strong>
                                <label for="its_me" class="label_its_me">Мы попросим вас подтвердить что именно вы явлетесь владельцем или доверенным лицом этой орагиназации, для этого могут потребоваться фотографии документов</label>
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




                        <div>
                            <label for="">Улица</label>
                        </div>



                        <div class="col-12">
                            <label>Логотип</label>

                            <!-- Квадрат для выбора -->
                            <div id="photoPicker">+</div>

                            <!-- Скрытый input -->
                            <input type="file" id="doctorPhotoInput" name="photo" accept="image/*">

                            <!-- Превью -->
                            <img id="doctorPhotoPreview" class="mt-2">
                        </div>

                        <div class="col-12">
                            <label>Расскажите об организации</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Опишите род деятельности, направления"></textarea>
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

<script>
    console.log("add_doctor.js loaded");

    /* ============================================================================
       ГЛАВНАЯ ФУНКЦИЯ — ВСЯ ЛОГИКА ДЛЯ МОДАЛКИ
    ============================================================================ */
    function initAddDoctorScripts(modal) {
        console.log("Add Doctor modal initialized!");

        /* ============================================================
           БЛОК 1 — Ограничение стажа от возраста
        ============================================================ */
        const dobInput = modal.querySelector('#date_of_birth');
        const expInput = modal.querySelector('#experience');

        if (dobInput && expInput) {
            dobInput.addEventListener('change', function() {
                const dob = new Date(this.value);
                if (isNaN(dob)) return;

                const now = new Date();
                const age =
                    now.getFullYear() -
                    dob.getFullYear() -
                    (now.getMonth() < dob.getMonth() ||
                        (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate()) ?
                        1 :
                        0);

                const maxExperience = age - 18;

                expInput.max = maxExperience > 0 ? maxExperience : 0;
                if (expInput.value > expInput.max) {
                    expInput.value = expInput.max;
                }
            });
        }

        /* ============================================================
           БЛОК 2 — Загрузка клиник по городу (Choices.js)
        ============================================================ */
        const citySelect = modal.querySelector("#citySelect");
        const clinicSelect = modal.querySelector("#clinicSelect");
        let clinicChoices;

        function initClinicChoices() {
            if (clinicChoices) clinicChoices.destroy();
            clinicChoices = new Choices(clinicSelect, {
                searchPlaceholderValue: "Поиск клиники...",
                removeItemButton: true
            });
        }

        if (clinicSelect && citySelect) {
            initClinicChoices();

            citySelect.addEventListener("change", () => {
                const cityId = citySelect.value;

                clinicChoices.destroy();
                clinicSelect.innerHTML = `<option value="">Загрузка...</option>`;

                if (!cityId) {
                    clinicSelect.innerHTML = `<option value="">Сначала выберите город</option>`;
                    initClinicChoices();
                    return;
                }

                fetch(`/api/clinics/by-city/${cityId}`)
                    .then(r => r.json())
                    .then(list => {
                        clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;
                        list.forEach(c =>
                            clinicSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`
                        );
                        initClinicChoices();
                    })
                    .catch(() => {
                        clinicSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                        initClinicChoices();
                    });
            });
        }

        /* ============================================================
           БЛОК 3 — Сферы деятельности
        ============================================================ */
        const fieldSelect = modal.querySelector("#fieldOfActivitySelect");

        if (fieldSelect) {
            fetch("/api/fields/specialists")
                .then(r => r.json())
                .then(list => {
                    fieldSelect.innerHTML = `<option value="">Выберите сферу деятельности</option>`;
                    list.forEach(item => {
                        fieldSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                })
                .catch(() => {
                    fieldSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                });
        }

        /* ============================================================
           БЛОК 4 — AJAX отправка формы
        ============================================================ */
        const form = modal.querySelector("#addDoctorForm");
        const errBox = modal.querySelector("#doctorErrors");

        if (form) {
            form.addEventListener("submit", async e => {
                e.preventDefault();

                const data = new FormData(form);

                const res = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json"
                    },
                    body: data
                });

                const json = await res.json();

                if (json.errors) {
                    modal.querySelectorAll(".is-invalid")
                        .forEach(e => e.classList.remove("is-invalid"));

                    let html = "";
                    Object.keys(json.errors).forEach(name => {
                        html += `<div>${json.errors[name][0]}</div>`;
                        const input = modal.querySelector(`[name="${name}"]`);
                        if (input) input.classList.add("is-invalid");
                    });

                    errBox.innerHTML = html;
                    errBox.classList.remove("d-none");
                    errBox.scrollIntoView({
                        behavior: "smooth"
                    });
                    return;
                }

                errBox.classList.add("d-none");
                alert(json.message);
                location.reload();
            });
        }

        /* ============================================================
           БЛОК 5 — Фото + Cropper
        ============================================================ */
        const fileInput = modal.querySelector("#doctorPhotoInput");
        const preview = modal.querySelector("#doctorPhotoPreview");
        const picker = modal.querySelector("#photoPicker");

        if (fileInput && preview && picker) {

            picker.addEventListener("click", () => fileInput.click());
            preview.addEventListener("click", () => fileInput.click());

            function showPreview(file) {
                if (!file) return;
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
                picker.style.display = "none";
            }

            function fallbackAttach() {
                fileInput.addEventListener("change", () => {
                    const f = fileInput.files?.[0];
                    if (!f) {
                        preview.style.display = "none";
                        picker.style.display = "flex";
                        return;
                    }
                    showPreview(f);
                });
            }

            try {
                if (typeof initCropper === "function") {
                    initCropper(fileInput, preview);

                    fileInput.addEventListener("change", () => {
                        const f = fileInput.files?.[0];
                        if (f) showPreview(f);
                        else {
                            preview.style.display = "none";
                            picker.style.display = "flex";
                        }
                    });

                } else {
                    fallbackAttach();
                }
            } catch {
                fallbackAttach();
            }

            preview.addEventListener("dblclick", () => {
                preview.src = "";
                preview.style.display = "none";
                picker.style.display = "flex";
                fileInput.value = "";
            });
        }
    }

    /* ============================================================================
       ЗАПУСК ПРИ ОТКРЫТИИ МОДАЛКИ
    ============================================================================ */
    document.addEventListener("shown.bs.modal", function(event) {
        const modal = event.target;

        if (modal.id === "addOrganizationModal") {
            initAddDoctorScripts(modal);
        }
    });

/* ============================================================
   ЗАВИСИМЫЕ СЕЛЕКТЫ: РЕГИОН → ГОРОДА + ПОИСК (Choices.js)
============================================================ */

let regionChoices, cityChoices;

function initRegionChoices() {
    if (regionChoices) regionChoices.destroy();
    regionChoices = new Choices("#regionSelect", {
        searchPlaceholderValue: "Поиск региона...",
        removeItemButton: true,
        shouldSort: false,
    });
}

function initCityChoices() {
    if (cityChoices) cityChoices.destroy();
    cityChoices = new Choices("#citySelect", {
        searchPlaceholderValue: "Поиск города...",
        removeItemButton: true,
        shouldSort: false,
    });
}

document.addEventListener("DOMContentLoaded", function () {

    initRegionChoices();
    initCityChoices();

    const regionSelect = document.querySelector("#regionSelect");
    const citySelect   = document.querySelector("#citySelect");

    regionSelect.addEventListener("change", function () {
        const region = this.value;

        if (!region) {
            citySelect.innerHTML = `<option value="">Сначала выберите регион</option>`;
            initCityChoices();
            return;
        }

        fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
            .then(r => r.json())
            .then(list => {
                citySelect.innerHTML = `<option value="">Выберите город</option>`;
                list.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                });
                initCityChoices();
            })
            .catch(() => {
                citySelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                initCityChoices();
            });
    });

});

document.addEventListener("DOMContentLoaded", function () {
    // Если используешь Choices.js — убедись, что он подключён
    let cityChoices = null;
    function initCityChoices() {
        if (cityChoices) cityChoices.destroy();
        cityChoices = new Choices('#citySelect', {
            searchPlaceholderValue: "Поиск города...",
            shouldSort: false,
            removeItemButton: false
        });
    }

    // инициализация пустого Choices
    initCityChoices();
    const regionSelect = document.getElementById('regionSelect');
    const citySelect = document.getElementById('citySelect');
    if (!regionSelect || !citySelect) return;
    regionSelect.addEventListener('change', function () {
        const region = this.value;
        // сбрасываем Choices, показываем прогресс
        cityChoices.destroy();
        citySelect.innerHTML = `<option value="">Загрузка...</option>`;
        initCityChoices();
        if (!region) {
            cityChoices.destroy();
            citySelect.innerHTML = `<option value="">Сначала выберите регион</option>`;
            initCityChoices();
            return;
        }

        // Кодируем region (в URL может быть пробел/слеш и т.п.)
        const url = `/api/cities/by-region/${encodeURIComponent(region)}`;

        fetch(url)
            .then(resp => {
                if (!resp.ok) throw new Error('Network response was not ok');
                return resp.json();
            })
            .then(list => {
                // list должен быть массив объектов {id, name}
                cityChoices.destroy();
                if (!Array.isArray(list) || list.length === 0) {
                    citySelect.innerHTML = `<option value="">Города не найдены</option>`;
                    initCityChoices();
                    return;
                }

                citySelect.innerHTML = `<option value="">Выберите город</option>`;
                list.forEach(c => {
                    // на всякий случай защита от некорректных элементов
                    if (c && c.id && c.name) {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        citySelect.appendChild(opt);
                    }
                });

                initCityChoices();
            })
            .catch(err => {
                console.error('Ошибка загрузки городов:', err);
                cityChoices.destroy();
                citySelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                initCityChoices();
            });
    });
});

</script>