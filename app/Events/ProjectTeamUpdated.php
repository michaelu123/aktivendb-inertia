<?php

namespace App\Events;

use App\Models\ProjectTeam;

class ProjectTeamUpdated extends Event
{
    public $project_team;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ProjectTeam $project_team)
    {
        $this->project_team = $project_team;
    }
}
