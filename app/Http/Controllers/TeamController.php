<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeamMember;
use App\Models\Team;
use App\Models\MemberRole;
use App\Models\Member;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teams = Team::orderBy("name")->get();
        // $teams = $teams->filter(
        //     fn($t) => Gate::allows("edit-team-details", $t->id)
        // )->values();

        return inertia(
            'Team/Index',
            [
                "teams" => $teams,
                // "teams" => Team::orderBy("name")->get(),
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $team = new Team;
        $team->id = -1;
        return inertia(
            'Team/Show',
            [
                "team" => $team,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin) {
            abort(403);
        }

        $all = $request->all();
        $v = $request->validate(
            [
                "name" => "required",
                "email" => "email|required",
            ]
        );
        Team::create($all);
        return redirect()->route("team.index")->with('success', "Neuer AG/OG-Eintrag wurde erzeugt");
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team, Request $request)
    {
        if (Gate::allows("edit-team-details", $team->id)) {
            $team->load([
                "members" => function ($query) {
                    $query->orderBy("last_name", "asc")->orderBy("first_name", "asc");
                }
            ])->with(["member_role"]);
            foreach ($team->members as $member) {
                $member->team_member->member_role_title = MemberRole::roleName($member->team_member->member_role_id);
            }
        }
        return inertia(
            'Team/Show',
            [
                "team" => $team,
            ]
        );
    }

    public function showWithDialog(Team $team, Request $request)
    {
        $memberIndex = $request->query("memberIndex");
        if (Gate::allows("edit-team-details", $team->id)) {
            $team->load([
                "members" => function ($query) {
                    $query->orderBy("last_name", "asc")->orderBy("first_name", "asc");
                }
            ]);
            $teamMemberNames = $team->members->map(fn($m) => $m["last_name"] . ", " . $m["first_name"])->toArray();
        }
        $allMembers = Member::orderBy("last_name")
            ->orderBy("first_name")
            ->get()
            ->map(fn($m) => ["name" => $m["last_name"] . ", " . $m["first_name"], "id" => $m->id])
            // oh boy, this ->values() took me hours...
            ->filter(fn($m) => !in_array($m["name"], $teamMemberNames))->values();
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
            ]
        );
    }

    public function indexWithHistory(Team $team, Request $request)
    {
        if (!Gate::allows('readhistory')) {
            abort(403);
        }
        $id = $request->all()["id"]; // TODO? get?
        $history = HistoryController::getByTableAndId("project_teams", $id);
        $users = User::all()->map(fn($u) => ["email" => $u->email, "id" => $u->id]);
        // $teams = Team::all()->map(fn($t) => ["name" => $t->name, "id" => $t->id]);
        $members = Member::all()->map(fn($m) => ["name" => $m->last_name . ", " . $m->first_name, "id" => $m->id]);
        $teams = [$team];

        return inertia(
            'Team/Index',
            [
                "members" => $members,
                "teams" => $teams,
                "users" => $users,
                "history" => $history,
                "retour" => "team.index"
            ]
        );
    }



    public function members(Request $request, int $team_id)
    {
        // this function is only called via fetch from exportExcel (like an api function)


        if (!Gate::allows("edit-team-details", $team_id)) {
            return [
                "members" => []
            ];
        }

        $t = Team::find($team_id);
        $t->load([
            "members" => function ($query) {
                $query->orderBy("last_name", "asc")->orderBy("first_name", "asc");
            }
        ]);
        return [
            "members" => $t->members
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
        if (!Gate::allows("edit-team-details", $team->id)) {
            abort(403);
        }
        $v = $request->validate([
            "name" => "required",
            "email" => "email|required",
            "description" => "nullable",
            "comments" => "nullable",
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

    private $tmHelper;
    public function __construct()
    {
        $this->tmHelper = new TeamMemberHelper(
            "Mitgliedseintrag wurde AG/OG hinzugefügt",
            "Mitgliedseintrag in AG/OG wurde geändert",
            "Mitgliedseintrag wurde aus AG/OG gelöscht"
        );
    }

    public function updateTM(Request $request)
    {
        return $this->tmHelper->updateTM($request, "team");
    }

    public function storeTM(Request $request)
    {
        return $this->tmHelper->storeTM($request, "team");
    }

    public function destroyTM(Request $request, int $id)
    {
        return $this->tmHelper->destroyTM($request, $id);
    }

}
