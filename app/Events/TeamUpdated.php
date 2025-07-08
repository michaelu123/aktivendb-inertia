<?php

namespace App\Events;

use App\Models\Team;

class TeamUpdated extends Event
{
    public $team;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }
}
