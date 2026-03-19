<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamSbMember extends Pivot
{
  protected $table = "team_sbmembers";

  protected $fillable = [
    'sbmember_id',
    'team_id',
    'aktion'
  ];


  // Relationships

  public function sbMember()
  {
    return $this->belongsTo('App\Models\SbMember');
  }

  public function team()
  {
    return $this->belongsTo('App\Models\Team', "team_id");
  }
}
