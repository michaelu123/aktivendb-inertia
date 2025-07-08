<?php

namespace App\Events;

use App\Models\TeamMember;

class TeamMemberUpdated extends Event
{
    public $team_member;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TeamMember $team_member)
    {
        $this->team_member = $team_member;
    }
}
