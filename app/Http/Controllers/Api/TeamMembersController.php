<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\TeamMember;
use App\Http\Controllers\Controller;

class TeamMembersController extends Controller
{
    const MODEL = "App\Models\TeamMember";
    use RESTActions;

    /*
    public function put(Request $request, $id)
    {
        $m = self::MODEL;
        $team_member = TeamMember::find($id);

        if (is_null($team_member)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }

        $team_member->member_role_id = (int) $request->get('member_role_id');
        $team_member->admin_comments = (string) $request->get('admin_comments');
        $team_member->save();

        $team_member->member_role_title = $team_member->member_role->title;

        return $this->respond(Response::HTTP_OK, $team_member);
    }
    */

    /*
    public function add(Request $request)
    {
        $m = self::MODEL;
        $team_member = new TeamMember();

        $team_member->member_id = (int) $request->get('member_id');
        $team_member->team_id = (int) $request->get('project_team_id');
        $team_member->member_role_id = (int) $request->get('member_role_id');
        $team_member->admin_comments = (string) $request->get('admin_comments');

        $team_member->save();

        $team_member->load('team');

        $team_member->member_role_title = $team_member->member_role->title;

        unset($team_member->team);

        return $this->respond(Response::HTTP_CREATED, $team_member);
    }
    */
}
