<?php

namespace App\Observers;

use App\Events\MemberUpdated;
use Illuminate\Support\Facades\Event;

trait MemberObserver {
    protected static function boot()
    {
        parent::boot();

        static::created(function($member) {
            Event::dispatch(new MemberUpdated($member));
        });

        static::updated(function($member) {
            Event::dispatch(new MemberUpdated($member));
        });

        static::deleted(function($member) {
            Event::dispatch(new MemberUpdated($member));
        });
    }
}