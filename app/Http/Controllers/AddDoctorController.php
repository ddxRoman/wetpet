<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\FieldOfActivity;

class AddDoctorController extends Controller
{
public function store(Request $request)
{
    // 🔹 1. Валидация данных
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'date_of_birth'     => 'required|date',
        'field_of_activity_id' => 'required|integer|exists:field_of_activities,id',
        'city_id'           => 'required|integer',
        'experience'        => 'nullable|integer|min:0',
        'exotic_animals'    => 'required|string',
        'On_site_assistance'=> 'required|string',
        'description'       => 'nullable|string',
        'photo'             => 'nullable|image|max:2048',
    ], [
        'name.required' => 'Введите имя специалиста.',
        'date_of_birth.required' => 'Укажите дату рождения.',
        'field_of_activity_id.required' => 'Выберите сферу деятельности.',
        'field_of_activity_id.exists' => 'Сфера деятельности не найдена.',
        'city_id.required' => 'Выберите город.',
        'exotic_animals.required' => 'Укажите работает ли с экзотическими животными.',
        'On_site_assistance.required' => 'Укажите выезд на дом.',
        'photo.image' => 'Файл должен быть картинкой.',
        'photo.max' => 'Максимальный размер фото — 2 МБ.',
    ]);

    // 🔹 2. Получаем объект сферы деятельнсти
    $field = FieldOfActivity::find($request->field_of_activity_id);

    // 🔹 3. Определяем, куда сохранять (врач или специалист)
    $model = ($field->activity == 'doctor')
        ? new Doctor()
        : new Specialist();

    // 🔥 4. Сохранение фото
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('doctors', 'public');
    }

    // 🔹 5. Запись данных
    $model->name = $request->name;
    $model->specialization = $field->name;
    $model->date_of_birth = $request->date_of_birth;
    $model->city_id = $request->city_id;
    $model->clinic_id = $request->clinic;
    $model->experience = $request->experience;
    $model->exotic_animals = $request->exotic_animals;
    $model->On_site_assistance = $request->On_site_assistance;
    $model->photo = $photoPath;
    $model->description = $request->description;

    $model->save();

    return response()->json([
        'success' => true,
        'message' => ($field->activity == 'doctor')
            ? 'Ветеринар успешно добавлен.'
            : 'Специалист успешно добавлен.'
    ]);
}


}
?>