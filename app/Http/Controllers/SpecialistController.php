<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\FieldOfActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class SpecialistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|string',
            'organization_id' => 'nullable|string',
            'description' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'experience' => 'nullable|integer',
            'exotic_animals' => 'nullable|string',
            'On_site_assistance' => 'nullable|string',
        ]);

        $field = FieldOfActivity::findOrFail($request->field_of_activity_id);

        $specialist = Specialist::create([
            'name' => $request->name,
            'specialization' => $field->name,
            'city_id' => $request->city_id,
            'organization_id' => $request->organization_id,
            'date_of_birth' => $request->date_of_birth,
            'experience' => $request->experience,
            'exotic_animals' => $request->exotic_animals,
            'On_site_assistance' => $request->On_site_assistance,
            'description' => $request->description,
        ]);

        // 🔔 Telegram
        try {
            Http::post(
                'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage',
                [
                    'chat_id' => config('services.telegram.chat_id'),
                    'text' => "➕ Новый специалист:\n{$specialist->name}\nСпециализация: {$specialist->specialization}"
                ]
            );
        } catch (\Throwable $e) {
            logger()->error('Telegram notify failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'id' => $specialist->id,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Specialist $specialist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Specialist $specialist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specialist $specialist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialist $specialist)
    {
        //
    }
}
