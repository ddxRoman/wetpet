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
fetch("/api/fields/specialists?activity=Vetclinic")

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
};
