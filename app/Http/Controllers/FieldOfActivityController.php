<?php

namespace App\Http\Controllers;
use App\Models\FieldOfActivity;

use Illuminate\Http\Request;

class FieldOfActivityController extends Controller
{


public function getSpecialists()
{
    return response()->json(
        \App\Models\FieldOfActivity::where('type', 'specialist')->orderBy('name')->get()
    );
}
public function getVetclinic()
{
    return FieldOfActivity::where('type', 'organization')->get();
}


}
