<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\TeamMember;

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

  public function updateTM(Request $request, string $from)
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
    $v["admin_comments"] ??= "";
    $tm->update($v);
    if ($from == "member") {
      return redirect()
        ->route("member.show", ["member" => $memberId])
        ->with('success', $this->updateMessage);
    } else {
      return redirect()
        ->route("team.show", ["team" => $teamId])
        ->with('success', $this->updateMessage);
    }
  }

  public function storeTM(Request $request, string $from)
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
    $v["admin_comments"] ??= "";
    TeamMember::create($v);
    if ($from == "member") {
      return redirect()->route("member.show", ["member" => $memberId])->with('success', $this->storeMessage);
    } else {
      return redirect()->route("team.show", ["team" => $teamId])->with('success', $this->storeMessage);
    }
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