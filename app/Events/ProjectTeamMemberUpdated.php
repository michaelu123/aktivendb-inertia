<?php

namespace App\Events;

use App\Models\ProjectTeamMember;

class ProjectTeamMemberUpdated extends Event
{
    public $project_team_member;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ProjectTeamMember $project_team_member)
    {
        $this->project_team_member = $project_team_member;
    }
}
