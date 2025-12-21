<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use Illuminate\Http\Request;

class SpecialistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function test()
    {
        dd(session()->all());

    }
}
