<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;

class Member extends Model
{
    use SoftDeletes;
    // TODO use App\Observers\MemberObserver;

    public $with_details = true;

    protected $appends = [
        'with_details'
    ];

    protected $fillable = [
        'name',
        'email_adfc',
        'email_private',
        'adfc_id',
        'phone_primary',
        'phone_secondary',
        'address',
        'admin_comments',
        'latest_contact',
        'latest_first_aid_training',
        'interests',
        'gender',
        'active',
        'status',
        'registered_for_first_aid_training',
        'next_first_aid_training',
        'birthday',
        'first_name',
        'last_name',
        'responded_to_questionaire',
        'responded_to_questionaire_at',
        'dsgvo_signature',
        'police_certificate'
    ];

    protected $restricted = [
        'email_adfc',
        'email_private',
        'adfc_id',
        'phone_primary',
        'phone_secondary',
        'address',
        'admin_comments',
        'latest_contact',
        'latest_first_aid_training',
        'interests',
        'created_at',
        'deleted_at',
        'gender',
        'status',
        'registered_for_first_aid_training',
        'next_first_aid_training',
        'birthday',
        'updated_at',
        'user',
        'responded_to_questionaire',
        'responded_to_questionaire_at',
        'dsgvo_signature',
        'police_certificate'
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
        ];

    // protected $with = [   // MUH With this, we add the user to a member if applicable, but why?
    //     'user'
    // ];

    public function getWithDetailsAttribute()
    {
        return Gate::allows('see-member-details', $this->id);
    }

    public static function create(array $attributes = [])
    {
        $model = static::query()->create($attributes);

        //    if (array_key_exists('createUser', $attributes) && $attributes['createUser'])
        //    {
        //      $user = $model->user()->create([
        //        'email_adfc' => $attributes['email'],
        //        'email_private' => $attributes['email'],
        //        'password' => 'abc123' // TODO: Passwort nicht statisch speichern
        //      ]);
        //    }

        return $model;
    }

    public function toJson($options = 0)
    {
        if (Gate::denies('see-member-details', $this->id)) {
            $this->hidden = array_merge($this->hidden, $this->restricted);
            $this->with_details = false;
        } else {
            $this->with_details = true;
        }
        return parent::toJson($options);
    }

    public function toArray()
    {
        if (Gate::denies('see-member-details', $this->id)) {
            $this->hidden = array_merge($this->hidden, $this->restricted);
            $this->with_details = false;
        } else {
            $this->with_details = true;
        }
        return parent::toArray();
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->restricted) && Gate::denies('see-member-details', $this->id)) {
            return null;
        } else {
            return parent::getAttribute($key);
        }
    }

    // Relationships

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function project_teams()
    {
        return $this->belongsToMany('App\Models\ProjectTeam', 'project_team_member')->whereNull('project_team_member.deleted_at')->withPivot(['id', 'member_role_id', 'admin_comments'])->using('App\Models\ProjectTeamMember')->as('project_team_member');
    }


}
