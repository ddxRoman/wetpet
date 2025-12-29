<?php

namespace App\Http\Controllers;
use App\Models\FieldOfActivity;

use Illuminate\Http\Request;

class FieldOfActivityController extends Controller
{


public function getSpecialists()
{
    return FieldOfActivity::where('type', 'specialist')
        ->orderBy('activity')
        ->orderBy('name')
        ->get();
}

public function getVetclinic()
{
    return FieldOfActivity::where('type', 'organization')->get();
}


}
