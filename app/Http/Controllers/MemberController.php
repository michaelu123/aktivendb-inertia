<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberRole;
use App\Models\ProjectTeam;
use App\Models\ProjectTeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia(
            'Member/Index',
            [
                "members" => Member::orderBy("last_name")->get()
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $member = new Member;
        $member->id = -1;
        return inertia(
            'Member/Show',
            ["member" => $member]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $all = $request->all();
        $v = $request->validate(
            [
                "first_name" => "required",
                "last_name" => "required",
                "birthday" => "decimal:4|nullable",
                'email_adfc' => 'email|nullable',
                'email_private' => 'email|nullable'

            ]
        );
        Member::create($all);
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member, Request $request)
    {
        $sess = $request->session();
        $readonly = $request->query("readonly", null);
        $store = $sess->get("store", []);
        if ($readonly !== null) {
            $store["readonly1"] = !!$readonly;
            $sess->put("store", $store);
        }
        $member->load(["project_teams"]);
        foreach ($member->project_teams as $project_team) {
            $project_team->project_team_member->member_role_title = $project_team->project_team_member->member_role->title;
        }
        return inertia(
            'Member/Show',
            ["member" => $member, "store" => $store]
        );
    }

    public function showWithDialog(Member $member, Request $request)
    {
        $sess = $request->session();
        $readonly = !!$request->query("readonly", true);
        $store = $sess->get("store", []);
        $store["readonly2"] = $readonly;
        $sess->put("store", $store);
        $teamIndex = $request->query("teamIndex");
        $member->load(["project_teams"]);
        $allProjectTeams = ProjectTeam::orderBy("name")->get()->map(fn($t) => ["name" => $t->name, "id" => $t->id]);
        $memberRoles = MemberRole::all()->map(fn($r) => ["title" => $r->title, "id" => $r->id]);
        foreach ($member->project_teams as $project_team) {
            $project_team->project_team_member->member_role_title = $project_team->project_team_member->member_role->title;
        }
        return inertia(
            'Member/Show',
            [
                "member" => $member,
                "teamToMemberDialogShown" => true,
                "teamIndex" => $teamIndex,
                "allProjectTeams" => $allProjectTeams,
                "memberRoles" => $memberRoles,
                "store" => $store,
            ]
        );
    }

    public function teams(Request $request, int $member_id)
    {
        if (!Gate::allows('see-member-details', $member_id)) {
            return [];
        }
        $m = Member::find($member_id);
        $m->load(["project_teams"]);
        return [
            "teams" => $m->project_teams->map(fn($team) => $team->name)
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $v = $request->validate([
            "first_name" => "required",
            "last_name" => "required",
            "birthday" => "date|nullable",
            'email_adfc' => 'email|nullable',
            'email_private' => 'email|nullable'

        ]);
        $member->update($v);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member, Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin) {
            $member->delete();
            return redirect()->back()->with('success', "Mitglied wurde gelöscht");
        } else {
            abort(403);
        }
    }

    public function updateTM(Request $request)
    {
        $v = $request->validate([
            "id" => "required",
            "member_role_id" => "required|min:1|max:3",
            "admin_comments" => "nullable",
            "member_id" => "required",
        ]);
        $tmid = $v["id"];
        $tm = ProjectTeamMember::find($tmid);
        $r = $tm->update($v);
        $member = Member::find($v["member_id"]);
        return $this->show($member, $request);
    }
    public function storeTM(Request $request)
    {
        $v = $request->validate([
            "project_team_id" => "required",
            "member_id" => "required",
            "member_role_id" => "required|min:1|max:3",
            "admin_comments" => "nullable",
        ]);
        ProjectTeamMember::create($v);
        $member = Member::find($v["member_id"]);
        return $this->show($member, $request);
    }
}
