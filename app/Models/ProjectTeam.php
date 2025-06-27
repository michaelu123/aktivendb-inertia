<?php
namespace App\Models;

// use App\Observers\ProjectTeamObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;

class ProjectTeam extends Model
{

  use SoftDeletes;
  // use ProjectTeamObserver;

  protected $appends = [
    'with_details'
  ];

  protected $fillable = [
    'name',
    'email',
    'description',
    'comments',
    'needs_first_aid_training'
  ];

  protected $dates = [
    'deleted_at'
  ];

  public static $rules = [
  ];

  public function getWithDetailsAttribute()
  {
    return Gate::allows('see-project-team-details', $this->id);
  }

  // Relationships

  public function members()
  {
    return $this->belongsToMany('App\Models\Member', 'project_team_member')->whereNull('project_team_member.deleted_at')->withPivot('id', 'member_role_id', 'admin_comments')->using('App\Models\ProjectTeamMember')->as('project_team_member');
  }

}
