<?php

namespace App\Observers;

use App\Events\TeamUpdated;
use Illuminate\Support\Facades\Event;

trait TeamObserver
{
    protected static function boot()
    {
        parent::boot();

        static::created(function ($team) {
            Event::dispatch(new TeamUpdated($team));
        });

        static::updated(function ($team) {
            Event::dispatch(new TeamUpdated($team));
        });

        static::deleted(function ($team) {
            Event::dispatch(new TeamUpdated($team));
        });
    }
}