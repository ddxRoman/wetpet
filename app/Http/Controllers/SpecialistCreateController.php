<?php
namespace App\Http\Controllers;
use App\Models\FieldOfActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SpecialistController;
use App\Http\Controllers\Controller;

class SpecialistCreateController extends Controller
{
    public function store(Request $request)
    {
        $field = FieldOfActivity::findOrFail(
            $request->field_of_activity_id
        );

        // защита
        if ($field->type !== 'specialist') {
            abort(403);
        }

        return match ($field->activity) {
            'doctor'     => app(DoctorController::class)->store($request),
            default      => app(SpecialistController::class)->store($request),
        };
    }
}
