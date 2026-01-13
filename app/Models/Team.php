<?php
namespace App\Models;

use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Observers\TeamObserver;

class Team extends Model
{

  use SoftDeletes;
  use TeamObserver;

  protected $table = "project_teams";
  public $apiCall = false;


  protected $appends = [
    'with_details'
  ];

  protected $fillable = [
    'name',
    'email',
    'description',
    'comments',
  ];

  protected $dates = [
    'deleted_at'
  ];

  public static $rules = [
  ];

  public function getWithDetailsAttribute()
  {
    // return true; // filtering done in TeamController
    return Gate::allows('edit-team-details', $this->id);
  }

  // Relationships

  public function members()
  {
    $res = $this->belongsToMany(
      'App\Models\Member',
      'project_team_member',
      "project_team_id",
      "member_id"
    )
      ->whereNull('project_team_member.deleted_at')
      ->withPivot('id', 'member_role_id', 'admin_comments')
      ->using('App\Models\TeamMember')
      ->as('team_member');
    return $res;
  }

  public function toJson($options = 0)
  {
    $j = parent::toJson($options);
    if ($this->apiCall) {
      $j = str_replace(
        "team_member",
        "project_team_member",
        $j
      );
    }
    return $j;
  }


}
