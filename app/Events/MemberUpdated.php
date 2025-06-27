<?php

namespace App\Events;

use App\Models\Member;

class MemberUpdated extends Event
{
    public $member;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }
}
