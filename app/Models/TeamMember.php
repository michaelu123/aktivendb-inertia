<?php

namespace App\Models;

use App\Observers\TeamMemberObserver;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMember extends Pivot
{
  use SoftDeletes;
  use TeamMemberObserver;

  protected $table = "project_team_member";

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

  public function team()
  {
    return $this->belongsTo('App\Models\Team', "project_team_id");
  }

  public function member_role()
  {
    return $this->belongsTo('App\Models\MemberRole');
  }
}
