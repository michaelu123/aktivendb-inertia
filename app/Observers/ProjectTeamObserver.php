<?php

namespace App\Observers;

use App\Events\ProjectTeamUpdated;
use Illuminate\Support\Facades\Event;

trait ProjectTeamObserver {
    protected static function boot()
    {
        parent::boot();

        static::created(function($project_team) {
            Event::dispatch(new ProjectTeamUpdated($project_team));
        });

        static::updated(function($project_team) {
            Event::dispatch(new ProjectTeamUpdated($project_team));
        });

        static::deleted(function($project_team) {
            Event::dispatch(new ProjectTeamUpdated($project_team));
        });
    }
}