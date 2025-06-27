<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'title'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public static $rules = [
        "title" => "required",
        "reference_name" => "required",
    ];

    public function members()
    {
        return $this->belongsToMany('App\Models\Member', 'ability_member')->whereNull('ability_member.deleted_at');
    }

    public function member_roles()
    {
        return $this->belongsToMany('App\Models\MemberRole', 'ability_member_role')->whereNull('ability_member_role.deleted_at');
    }
}