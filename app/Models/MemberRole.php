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

  public function team_members()
  {
    return $this->hasMany('TeamMember');
  }

  public function abilities()
  {
    return $this->belongsToMany('App\Models\Ability', 'ability_member_role')->whereNull('ability_member_role.deleted_at');
  }

  public static function roleName($id)
  {
    if ($id == 1)
      return "Vorsitz";
    if ($id == 2)
      return "Mitglied";
    if ($id == 3)
      return "Formales Mitglied";
    return "?";
  }

}
