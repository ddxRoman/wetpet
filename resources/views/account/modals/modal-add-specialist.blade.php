

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

<form id="addDoctorForm"
      method="POST"
      action="/add-doctor"
      enctype="multipart/form-data">
    @csrf


                <div class="modal-header">
                    <h5 class="modal-title">Добавление специалиста</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="doctorErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">

                        <div class="col-md-6">
    <label>Сфера деятельности</label>
    <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
        <option value="">Загрузка...</option>
    </select>
</div>


                        <div class="col-12">
                            <label>Имя врача</label>
                            <input type="text" name="name" class="form-control">
                        </div>


<div class="col-md-6">
    <label>Дата рождения</label>
    <input 
        type="date"
        id="date_of_birth"
        name="date_of_birth"
        class="form-control"
        max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
    >
</div>
<div class="col-md-6">
    <label>Стаж (лет)</label>
    <input 
        type="number" 
        id="experience"
        name="experience" 
        class="form-control"
        min="0"
    >
</div>



                                            <div class="col-12">
    <label class="form-check-label">
        <input type="checkbox" name="its_me" class="form-check-input">
    <strong>
        Добавляю себя
    </strong> 
    <label for="its_me" class="label_its_me">Мы попросим вас подтвердить что именно вы явлетесь этим специалистом, для этого могут потребоваться фотографии дипломов и документов</label>
    </label>
</div>


                        <div class="col-md-6">
                            <label>Город</label>
                            <select name="city_id" id="citySelect" class="form-select">
                                <option value="">Выберите город</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Клиника</label>
                            <select name="clinic" id="clinicSelect" class="form-select">
    <option value="">Сначала выберите город</option>
</select>

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
                        <div class="col-6">
                            <label>Почта</label>
                            <input type="text" name="mail" class="form-control">
                        </div>    


                        <div class="col-md-6">
                            <label>Экзотические животные</label>
                            <select name="exotic_animals" class="form-control">
                                <option value="Нет">Нет</option>
                                <option value="Да">Да</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Выезд на дом</label>
                            <select name="On_site_assistance" class="form-control">
                                <option value="Нет">Нет</option>
                                <option value="Да">Да</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Фото</label>

                            <!-- Квадрат для выбора -->
                            <div id="photoPicker">+</div>

                            <!-- Скрытый input -->
                            <input type="file" id="doctorPhotoInput" name="photo" accept="image/*">

                            <!-- Превью -->
                            <img id="doctorPhotoPreview" class="mt-2">
                        </div>

                        <div class="col-12">
                            <label>Расскажите о специалисте</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Опишите род деятельности, направления"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>

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
        dobInput.addEventListener('change', function () {
            const dob = new Date(this.value);
            if (isNaN(dob)) return;

            const now = new Date();
            const age =
                now.getFullYear() -
                dob.getFullYear() -
                (now.getMonth() < dob.getMonth() ||
                (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate())
                    ? 1
                    : 0);

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
                headers: { "Accept": "application/json" },
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
                errBox.scrollIntoView({ behavior: "smooth" });
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
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("addDoctorModal");
    if (modal) initAddDoctorScripts(modal);
});


</script>