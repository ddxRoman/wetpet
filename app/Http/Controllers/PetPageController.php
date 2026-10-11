<?php

namespace App\Http\Controllers;

use App\Models\Pet;

class PetPageController extends Controller
{
    /**
     * Отдельная страница питомца: /pet/{id}.
     * Доступна только для питомцев владельцев с активной премиум-подпиской.
     */
    public function show(Pet $pet)
    {
        $pet->load(['animal', 'user', 'photos']);

        $owner = $pet->user;

        abort_if(! $owner || $owner->status === 'ban' || ! $owner->isPremium(), 404);

        $title = $pet->name . ($pet->animal ? ' — ' . $pet->animal->species . ($pet->animal->breed ? ' (' . $pet->animal->breed . ')' : '') : '');

        $seoMeta = [
            'title'          => $title . ' — Зверозор',
            'description'    => 'Питомец ' . $pet->name . ' — владелец ' . $owner->name . '.',
            'og_title'       => $title . ' — Зверозор',
            'og_description' => 'Страница питомца ' . $pet->name,
            'robots'         => 'noindex, follow',
        ];

        return view('pages.pets.show', [
            'pet'     => $pet,
            'owner'   => $owner,
            'seoMeta' => $seoMeta,
        ]);
    }
}
