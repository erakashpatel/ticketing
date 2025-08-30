<?php

namespace App\Policies\V1;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, User $model) {
        // Demo user can do anything
        return true;
    }

    public function store(User $user) {
        // Demo user can do anything
        return true;
    }

    public function update(User $user, User $model) {
        // Demo user can do anything
        return true;
    }
}