<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'country'    => 'required|string|max:255',
            'region'     => 'nullable|string|max:255',
            'city'       => 'required|string|max:255',
            'street'     => 'required|string|max:255',
            'house'      => 'nullable|string|max:255',
            'description'=> 'nullable|string',
            'logo'       => 'nullable|string',
            'schedule'   => 'nullable|string|max:255',
            'workdays'   => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:255',
            'email'      => 'nullable|string|max:255',
            'type'       => 'required|string|max:255',
        ]);

        $organization = Organization::create($validated);

        return response()->json([
            'success' => true,
            'organization' => $organization
        ]);
    }
}
