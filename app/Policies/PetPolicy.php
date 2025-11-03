<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pet;

class PetPolicy
{
    public function view(User $user, Pet $pet)
    {
        return $pet->user_id === $user->id;
    }

    public function update(User $user, Pet $pet)
    {
        return $pet->user_id === $user->id;
    }

    public function delete(User $user, Pet $pet)
    {
        return $pet->user_id === $user->id;
    }

    public function create(User $user)
    {
        return true;
    }
}
