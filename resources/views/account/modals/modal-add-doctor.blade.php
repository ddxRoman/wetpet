<style>
    /* квадрат 150x150 */
    #photoPicker {
        width: 150px;
        height: 150px;
        border: 2px dashed #b8b8b8;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 48px;
        color: #999;
        transition: 0.2s;
    }
    #photoPicker:hover {
        background: #f7f7f7;
    }
    #doctorPhotoPreview {
        width: 150px;
        height: 150px;
        border-radius: 10px;
        object-fit: cover;
        display: none;
    }
    #doctorPhotoInput {
        display: none;
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="/js/add-doctor.js"></script>
<script src="/js/cropper-init.js"></script>

<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addDoctorForm" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Добавление специалиста</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div id="doctorErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">

                        <div class="col-12">
                            <label>Имя врача</label>
                            <input type="text" name="name" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Специализация</label>
                            <input type="text" name="specialization" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Дата рождения</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Опыт (лет)</label>
                            <input type="number" name="experience" class="form-control">
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
                            <input type="number" name="clinic_id" class="form-control">
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
                            <select name="On-site_assistance" class="form-control">
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
                            <label>Описание</label>
                            <textarea name="description" rows="4" class="form-control"></textarea>
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

<!-- Модал кропера -->
<div id="cropper-modal" 
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); 
            justify-content:center; align-items:center; z-index:2000;">

    <div style="background:#fff; padding:20px; border-radius:10px; max-width:90%; max-height:90%;">
        <img id="cropper-image" style="max-width:100%; max-height:70vh;">
        
        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-secondary" id="close-cropper">Отмена</button>
            <button class="btn btn-primary" id="save-cropped">Обрезать и сохранить</button>
        </div>
    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById("doctorPhotoInput");
    const preview = document.getElementById("doctorPhotoPreview");
    const picker = document.getElementById("photoPicker");

    if (!fileInput || !preview || !picker) {
        console.error("Photo elements not found:", { fileInput, preview, picker });
        return;
    }

    // Клик по квадрату вызывает выбор файла
    picker.addEventListener("click", () => fileInput.click());

    // Клик по превью – тоже вызывает выбор файла (возможность изменить картинку)
    preview.addEventListener("click", () => fileInput.click());

    // helper: показать превью из File или Blob
    function showPreviewFromFile(file) {
        if (!file) return;
        try {
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = "block";
            picker.style.display = "none";
        } catch (err) {
            console.error("showPreviewFromFile error:", err);
        }
    }

    // fallback на случай отсутствия кропера
    function fallbackAttach() {
        fileInput.addEventListener("change", () => {
            const f = fileInput.files && fileInput.files[0];
            if (!f) {
                preview.style.display = "none";
                picker.style.display = "flex";
                return;
            }
            showPreviewFromFile(f);
        });
    }

    // Подключаем кроппер
    try {
        if (typeof initCropper === 'function') {
            initCropper(fileInput, preview);

            fileInput.addEventListener("change", () => {
                const f = fileInput.files && fileInput.files[0];
                if (f) {
                    showPreviewFromFile(f);
                } else {
                    preview.style.display = "none";
                    picker.style.display = "flex";
                }
            });

            console.info("initCropper found and initialized.");
        } else {
            console.warn("initCropper() not found — using fallback preview only.");
            fallbackAttach();
        }
    } catch (err) {
        console.error("Error initializing cropper:", err);
        fallbackAttach();
    }

    // двойной клик по превью — сброс изображения
    preview.addEventListener('dblclick', () => {
        preview.src = '';
        preview.style.display = 'none';
        picker.style.display = 'flex';
        fileInput.value = '';
    });
});
</script>
