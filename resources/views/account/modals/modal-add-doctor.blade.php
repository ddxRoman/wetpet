<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="/js/add-doctor.js"></script>
<script src="/js/cropper-init.js"></script>

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
                                            <div class="col-12">
    <label class="form-check-label">
        <input type="checkbox" name="its_me" class="form-check-input">
    <strong>
        Добавляю себя
    </strong> 
    <label for="its_me" class="label_its_me">Мы попросим вас подтвердить что именно вы явлетесь этим специалистом, для этого могут потребоваться фотографии дипломов и документов</label>
    </label>
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
    <label>Сфера деятельности</label>
    <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
        <option value="">Загрузка...</option>
    </select>
</div>



                        <!-- <div class="col-12">
                            <label>Специализация</label>
                            <input type="text" name="specialization" class="form-control">
                        </div> -->



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
document.getElementById('date_of_birth').addEventListener('change', function() {
    const dob = new Date(this.value);
    if (isNaN(dob)) return;

    const now = new Date();
    const age = now.getFullYear() - dob.getFullYear() -
                ((now.getMonth() < dob.getMonth() || 
                 (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate())) ? 1 : 0);

    const maxExperience = age - 18;
    const expInput = document.getElementById('experience');

    expInput.max = maxExperience > 0 ? maxExperience : 0;

    // если стаж превышал — автоматически уменьшаем
    if (expInput.value > expInput.max) {
        expInput.value = expInput.max;
    }
});
</script>