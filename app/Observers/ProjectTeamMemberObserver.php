<?php

namespace App\Observers;

use App\Events\ProjectTeamMemberUpdated;
use Illuminate\Support\Facades\Event;

trait ProjectTeamMemberObserver {
    protected static function boot()
    {
        parent::boot();

        static::created(function($project_team_member) {
            Event::dispatch(new ProjectTeamMemberUpdated($project_team_member));
        });

        static::updated(function($project_team_member) {
            Event::dispatch(new ProjectTeamMemberUpdated($project_team_member));
        });

        static::deleted(function($project_team_member) {
            Event::dispatch(new ProjectTeamMemberUpdated($project_team_member));
        });
    }
}