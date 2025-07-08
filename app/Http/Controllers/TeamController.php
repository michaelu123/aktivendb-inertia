<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberRole;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sess = $request->session();

        $store = $sess->get("store", []);
        $pageno = $request->query("pageno", null);
        if ($pageno !== null) {
            $store["pageno"] = $pageno;
        }
        $sess->put("store", $store);

        return inertia(
            'Team/Index',
            [
                "teams" => Team::orderBy("name")->get(),
                "storeC" => $store
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $team = new Team;
        $team->id = -1;
        return inertia(
            'Team/Show',
            ["team" => $team]
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
                "name" => "required",
                "email" => "email|required",
            ]
        );
        Team::create($all);
        return redirect()->back()->with('success', "Neuer AG/OG-Eintrag wurde erzeugt");
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team, Request $request)
    {
        $sess = $request->session();

        $store = $sess->get("store", []);
        $readonly = $request->query("readonly", null);
        if ($readonly !== null) {
            $store["readonly1"] = !!$readonly;
        }
        $pageno = $request->query("pageno", null);
        if ($pageno !== null) {
            $store["pageno"] = $pageno;
        }
        $sess->put("store", $store);

        $team->load(["members"])->with(["member_role"]);
        foreach ($team->members as $member) {
            $member->team_member->member_role_title = MemberRole::roleName($member->team_member->member_role_id);
        }
        return inertia(
            'Team/Show',
            [
                "team" => $team,
                "storeC" => $store
            ]
        );
    }

    public function showWithDialog(Team $team, Request $request)
    {
        $sess = $request->session();

        $store = $sess->get("store", []);
        $readonly = !!$request->query("readonly", true);
        $store["readonly2"] = $readonly;
        $sess->put("store", $store);

        $memberIndex = $request->query("memberIndex");
        $team->load(["members"]);
        $allMembers = Member::orderBy("last_name")->orderBy("first_name")->get()->map(fn($m) => ["name" => $m->last_name . ", " . $m->first_name, "id" => $m->id]);
        $memberRoles = MemberRole::all()->map(fn($r) => ["title" => $r->title, "id" => $r->id]);
        foreach ($team->members as $member) {
            $member->member_role_title = MemberRole::roleName($member->team_member->member_role_id);
        }
        return inertia(
            'Team/Show',
            [
                "team" => $team,
                "memberToTeamDialogShown" => true,
                "memberIndex" => $memberIndex,
                "allMembers" => $allMembers,
                "memberRoles" => $memberRoles,
                "storeC" => $store
            ]
        );
    }

    public function members(Request $request, int $team_id)
    {
        // this function is only called via fetch from exportExcel (like an api function)
        $t = Team::find($team_id);
        $t->load(["members"]);
        return [
            "members" => $t->members->map(fn($member): string => $member->last_name . ", " . $member->first_name)
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $v = $request->validate([
            "name" => "required",
            "email" => "email|required",
        ]);
        $team->update($v);
        return redirect()->back()->with('success', "AG/OG-Eintrag wurde geändert");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team, Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin) {
            $team->delete();
            return redirect()->back()->with('success', "AG/OG-Eintrag wurde gelöscht");
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
            "team_id" => "required",
        ]);
        $tmid = $v["id"];
        $tm = TeamMember::find($tmid);
        $r = $tm->update($v);
        $team = Team::find($v["team_id"]);
        return $this->show($team, $request);
    }
    public function storeTM(Request $request)
    {
        $v = $request->validate([
            "team_id" => "required",
            "member_id" => "required",
            "member_role_id" => "required|min:1|max:3",
            "admin_comments" => "nullable",
        ]);
        TeamMember::create($v);
        $team = Team::find($v["team_id"]);
        return $this->show($team, $request);
    }

}
