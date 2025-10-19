<?php

namespace App\Http\Controllers;

use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
    $doctors = \App\Models\Doctor::all();
    return view('welcome', compact('doctors')); // <- важно: 'welcome'
    }
}
