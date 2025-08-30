<?php

namespace App\Policies\V1;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, Ticket $ticket) {
        // Demo user can do anything
        return true;
    }

    public function store(User $user) {
        // Demo user can do anything
        return true;
    }

    public function update(User $user, Ticket $ticket) {
        // Demo user can do anything
        return true;
    }
}