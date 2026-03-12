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
        'email_adfc',
        'phone_primary',
        'phone_secondary',
        'email_private',
        'address',
        'adfc_id',
        'admin_comments',
        'latest_first_aid_training',
        'gender',
        'interests',
        'latest_contact',
        'active',
        'birthday',
        'status',
        'responded_to_questionaire',
        'responded_to_questionaire_at',
        'dsgvo_signature',
        'police_certificate',
        'polcert_date',
    ];

    protected $dates = [
        'deleted_at'
    ];

    public static $rules =
        [
            'email_adfc' => 'email',
            'email_private' => 'email',
            'dsgvo_signature' => 'nullable|in:0,1,2',
            'police_certificate' => 'nullable|in:0,1,2',
            'polcert_date' => 'nullable|date',
        ];


    public static function create(array $attributes = [])
    {
        $model = static::query()->create($attributes);
        return $model;
    }


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
