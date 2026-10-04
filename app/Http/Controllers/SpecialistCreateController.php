<?php

namespace App\Http\Controllers;

use App\Models\FieldOfActivity;
use App\Models\Doctor;
use App\Models\Specialist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialistCreateController extends Controller
{
    public function store(Request $request)
    {
        $field = FieldOfActivity::findOrFail($request->field_of_activity_id);

        // 🔒 защита
        if ($field->type !== 'specialist') {
            abort(403);
        }

        // 🔹 создаём сущность
        $response = match ($field->activity) {
            'doctor' => app(DoctorController::class)->store($request),
            default  => app(SpecialistController::class)->store($request),
        };

        // Найден похожий врач/специалист (ответ 409) — ничего не создано, привязывать владельца не к чему
        if ($response->getStatusCode() === 409 || empty($response->getData(true)['id'] ?? null)) {
            return $response;
        }

        // 🔹 привязка владельца
        if ($request->boolean('its_me') && Auth::check()) {

            $data = $response->getData(true);
            $userId = Auth::id();

            if ($field->activity === 'doctor') {

                $doctor = Doctor::find($data['id']);

                $doctor?->owners()->syncWithoutDetaching([
                    $userId => ['is_confirmed' => false],
                ]);

            } else {

                $specialist = Specialist::find($data['id']);

                $specialist?->owners()->syncWithoutDetaching([
                    $userId => ['is_confirmed' => false],
                ]);
            }
        }

        return $response;
    }
}
