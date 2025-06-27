<?php

namespace App\Models;

// use App\Observers\ProjectTeamMemberObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectTeamMember extends Pivot
{
  use SoftDeletes;
  // use ProjectTeamMemberObserver;

  public $incrementing = true;

  protected $fillable = [

  ];

  protected $dates = [
    'deleted_at'
  ];

  public static $rules = [

  ];

  // Relationships

  public function member()
  {
    return $this->belongsTo('App\Models\Member');
  }

  public function project_team()
  {
    return $this->belongsTo('App\Models\ProjectTeam');
  }

  public function member_role()
  {
    return $this->belongsTo('App\Models\MemberRole');
  }
}
