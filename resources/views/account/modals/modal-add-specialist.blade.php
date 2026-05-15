<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

<form id="addDoctorForm"
      method="POST"
      action="{{ route('specialist.store') }}"
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
                                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label>Стаж (лет)</label>
                            <input
                                type="number"
                                id="experience"
                                name="experience"
                                class="form-control"
                                min="0">
                        </div>



                        @if(auth()->check() && auth()->user()->canAddSelfSpecialist())
                        <div class="col-12 form-check-label">
                            <label>
                                <input type="checkbox" name="its_me" class="form-check-input">
                                <strong>Добавляю себя</strong>
                                <div class="label_its_me">
                                    Мы попросим подтвердить, что это вы.
                                </div>
                            </label>
                        </div>
                        @endif



                        <div class="col-md-6">
                            <label>Регион</label>
                            <select name="region" id="regionSelect" class="form-select">
                                <option value="">Выберите регион</option>
                                @foreach($cities->unique('region') as $city)
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



                        <div class="col-md-6">
                            <label>Оргнанизация </label>
                            <select name="clinic_id" id="clinicSelect" class="form-select">
                                <option value="">Сначала выберите город</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_private">
                            <label class="form-check-label" for="is_private">Я частный специалист (работаю без привязки к клинике/центру)</label>
                        </div>

                        <div id="address-section-add" style="display: none;">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="street" class="form-label">Улица</label>
                                    <input type="text" name="street" id="street"
                                        class="form-control"
                                        placeholder="Напр. ул. Мира">
                                </div>
                                <div class="col-md-6">
                                    <label for="house" class="form-label">Дом</label>
                                    <input type="text" name="house" id="house"
                                        class="form-control"
                                        placeholder="Напр. 10/1">
                                </div>
                            </div>
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


                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="exotic_animals" id="exotic_animals" value="Да">
                                <label class="form-check-label" for="exotic_animals">Работаю с экзотическими животными</label>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="On_site_assistance" id="On_site_assistance" value="Да">
                                <label class="form-check-label" for="On_site_assistance">Выезд на дом</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label>Фото специалиста</label>
                            <div class="photo-wrapper">
                                <input type="file" name="photo" id="doctorPhotoInput" class="d-none" accept="image/*">

                                <div id="doctorPhotoPicker" style="cursor:pointer; width:100px; height:100px; border:2px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:2rem;">+</div>

                                <div id="photoPreviewWrapper" style="display:none;">
                                    <img id="doctorPhotoPreview" title="Предпросмотр" style="width:100px; height:100px; object-fit:cover;">
                                    <button type="button" id="removePhotoBtn" class="btn btn-danger btn-sm">&times;</button>
                                </div>
                            </div>
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