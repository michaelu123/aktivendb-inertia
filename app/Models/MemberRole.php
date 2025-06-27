<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberRole extends Model
{

  use SoftDeletes;

  protected $fillable = [
    'title',
    'description'
  ];

  protected $dates = [
    'deleted_at'
  ];

  public static $rules = [
    "title" => "required",
    "reference_name" => "required",
  ];

  // Relationships

  public function project_team_members()
  {
    return $this->hasMany('ProjectTeamMember');
  }

  public function abilities()
  {
    return $this->belongsToMany('App\Models\Ability', 'ability_member_role')->whereNull('ability_member_role.deleted_at');
  }

}
