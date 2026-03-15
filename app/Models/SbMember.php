<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SbMember extends Model
{
    protected $table = "sbmembers";

    protected $fillable = [
        'member_id',
        'first_name',
        'last_name',
        'speicherungok',
        'aktiv',
        'email_adfc',
        'email_private',
        'phone_primary',
        'phone_secondary',
        'address',
        'adfc_id',
        'gender',
        'interests',
        'birthday',
        'eingetragen',
        'admin_comments',
    ];

    // Relationships

    public function member()
    {
        return $this->hasOne(Member::class, "id", "member_id");
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Models\Team',
            'team_sbmembers',
            "sbmember_id",
            "team_id"
        )
            ->using('App\Models\TeamSbMember')
            ->as('team_sbmembers');
    }
}
