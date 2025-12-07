document.addEventListener("DOMContentLoaded", () => {

    /* ============================================================
       БЛОК 1 — ФОТО + CROPPER
    ============================================================ */
    const fileInput  = document.getElementById("doctorPhotoInput");
    const preview    = document.getElementById("doctorPhotoPreview");
    const picker     = document.getElementById("photoPicker");

    if (fileInput && preview && picker) {

        picker.addEventListener("click", () => fileInput.click());
        preview.addEventListener("click", () => fileInput.click());

        function showPreview(file) {
            if (!file) return;
            const url = URL.createObjectURL(file);
            preview.src = url;
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

    /* ============================================================
       БЛОК 2 — загрузка клиник по городу (Choices.js)
    ============================================================ */
    const citySelect = document.getElementById("citySelect");
    const clinicSelect = document.getElementById("clinicSelect");
    let clinicChoices;

    function initClinicChoices() {
        if (clinicChoices) clinicChoices.destroy();
        clinicChoices = new Choices("#clinicSelect", {
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
                    list.forEach(c => {
                        clinicSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                    initClinicChoices();
                })
                .catch(() => {
                    clinicSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                    initClinicChoices();
                });
        });
    }

    /* ============================================================
       БЛОК 3 — загрузка сфер деятельности
    ============================================================ */
    const fieldSelect = document.getElementById("fieldOfActivitySelect");

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
       БЛОК 4 — дата рождения + ограничение стажа
    ============================================================ */
    const birthInput = document.getElementById("dateOfBirth");
    const expInput   = document.getElementById("experienceInput");
    const modal      = document.getElementById("addDoctorModal");

    function birthMax() {
        const d = new Date();
        d.setFullYear(d.getFullYear() - 18);
        return d.toISOString().split("T")[0];
    }

    function calcAge(d) {
        const dob = new Date(d);
        const now = new Date();
        let age = now.getFullYear() - dob.getFullYear();
        const m = now.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
        return age;
    }

    function updateExp() {
        if (!birthInput.value) {
            expInput.removeAttribute("max");
            return;
        }

        const age = calcAge(birthInput.value);
        const maxExp = Math.max(age - 18, 0);

        expInput.max = maxExp;

        if (expInput.value > maxExp) expInput.value = maxExp;
    }

    if (birthInput && expInput) {
        if (modal) {
            modal.addEventListener("show.bs.modal", () => {
                birthInput.max = birthMax();
                updateExp();
            });
        }

        birthInput.addEventListener("change", updateExp);
    }

    /* ============================================================
       БЛОК 5 — AJAX отправка формы
    ============================================================ */
    const form = document.getElementById("addDoctorForm");
    const errBox = document.getElementById("doctorErrors");

    form?.addEventListener("submit", async e => {
        e.preventDefault();

        const data = new FormData(form);

        const res = await fetch(form.action, {
            method: "POST",
            headers: { "Accept": "application/json" },
            body: data
        });

        const json = await res.json();

        if (json.errors) {
            document.querySelectorAll(".is-invalid")
                .forEach(e => e.classList.remove("is-invalid"));

            let html = "";
            Object.keys(json.errors).forEach(name => {
                html += `<div>${json.errors[name][0]}</div>`;
                const input = document.querySelector(`[name="${name}"]`);
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

});
