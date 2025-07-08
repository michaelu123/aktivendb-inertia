<?php

namespace App\Observers;

use App\Events\TeamMemberUpdated;
use Illuminate\Support\Facades\Event;

trait TeamMemberObserver
{
    protected static function boot()
    {
        parent::boot();

        static::created(function ($team_member) {
            Event::dispatch(new TeamMemberUpdated($team_member));
        });

        static::updated(function ($team_member) {
            Event::dispatch(new TeamMemberUpdated($team_member));
        });

        static::deleted(function ($team_member) {
            Event::dispatch(new TeamMemberUpdated($team_member));
        });
    }
}