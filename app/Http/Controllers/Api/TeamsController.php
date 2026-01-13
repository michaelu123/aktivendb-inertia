<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Member;
use App\Http\Controllers\Controller;

class TeamsController extends Controller
{
    const MODEL = "App\Models\Team";
    use RESTActions;

    public function all(Request $request)
    {
        return Team::all();
    }

    public function get(Request $request, $id)
    {
        $team = Team::with(['members'])->find($id);
        foreach ($team->members as $member) {
            if ($member->team_member->member_role) {
                $member->team_member->member_role_title = $member->team_member->member_role->title;
            }
        }
        $team->with_details = true;
        $team->apiCall = true;

        return $team;
    }

}
