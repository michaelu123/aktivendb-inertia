<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamMemberHelper
{
  private $storeMessage = "";
  private $updateMessage = "";
  private $destroyMessage = "";

  public function __construct(string $smsg, $umsg, $dmsg)
  {
    $this->storeMessage = $smsg;
    $this->updateMessage = $umsg;
    $this->destroyMessage = $dmsg;
  }

  public function updateTM(Request $request)
  {
    $v = $request->validate([
      "id" => "required|min:0",
      "team_id" => "required|min:0",
      "member_id" => "required|min:0",
      "member_role_id" => "required|min:1|max:3",
      "admin_comments" => "nullable",
    ]);
    $tmid = $v["id"];
    $tm = TeamMember::find($tmid);
    $teamId = $v["team_id"];
    $memberId = $v["member_id"];
    if (!Gate::allows("edit-team-details", $teamId)) {
      abort(403);
    }
    if ($this->checkDuplicate($tmid, $teamId, $memberId)) {
      abort(409);
    }
    $v["project_team_id"] = $teamId; // Is there a better way for column rename?
    unset($v["team_id"]);
    $tm->update($v);
    return redirect()
      ->route("member.show", ["member" => $memberId])
      ->with('success', $this->updateMessage);
  }

  public function storeTM(Request $request)
  {
    $v = $request->validate([
      "team_id" => "required|min:0",
      "member_id" => "required|min:0",
      "member_role_id" => "required|min:1|max:3",
      "admin_comments" => "nullable",
    ]);
    $teamId = $v["team_id"];
    $memberId = $v["member_id"];
    if (!Gate::allows("edit-team-details", $teamId)) {
      abort(403);
    }
    if ($this->checkDuplicate(-1, $teamId, $memberId)) {
      abort(409);
    }
    $v["project_team_id"] = $teamId; // Is there a better way for column rename?
    unset($v["team_id"]);
    TeamMember::create($v);
    return redirect()->route("member.show", ["member" => $memberId])->with('success', $this->storeMessage);
  }

  public function destroyTM(Request $request, int $id)
  {
    $tm = TeamMember::find($id);
    if (!Gate::allows("edit-team-details", $tm->project_team_id)) {
      abort(403);
    }
    $tm->delete();
    return redirect()->back()->with('success', $this->destroyMessage);
  }

  private function checkDuplicate(int $tmid, int $teamId, int $memberId): bool
  {
    $query = TeamMember::where('project_team_id', $teamId)
      ->where('member_id', $memberId)
      ->where('id', '!=', $tmid);
    return $query->exists();
  }
}