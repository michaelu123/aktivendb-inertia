<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return inertia(
            'Member/Index',
            [
                "members" => Member::orderBy("last_name")->orderBy("first_name")->orderBy("first_name")->get(),
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin) {
            abort(403);
        }

        $member = new Member;
        $member->id = -1;
        return inertia(
            'Member/Show',
            [
                "member" => $member,
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
                "first_name" => "required",
                "last_name" => "required",
                "birthday" => "digits:4|nullable",
                'email_adfc' => 'email|nullable',
                'email_private' => 'email|nullable',
                'adfc_id' => "digits:8|nullable"
            ]
        );
        Member::create($all);
        return redirect()->route("member.index")->with('success', "Neuer Mitgliedseintrag wurde erzeugt");
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member, Request $request)
    {
        // if (!Gate::allows('see-member-details', $member->id)) {
        //     abort(403);
        // }
        $member->load([
            "teams" => function ($query) {
                $query->orderBy("name", "asc");
            }
        ]);
        $teams = $member->teams->filter(fn($team) => Gate::allows('edit-team-details', $team->id));
        foreach ($teams as $team) {
            $team->team_member->member_role_title = MemberRole::roleName($team->team_member->member_role_id);
        }
        $member->setRelation(
            'teams',
            $teams->values(),
        );
        return inertia(
            'Member/Show',
            [
                "member" => $member,
            ]
        );
    }

    public function showWithDialog(Member $member, Request $request)
    {
        // if (!Gate::allows('see-member-details', $member->id)) {
        //     abort(403);
        // }
        $teamIndex = $request->query("teamIndex");
        $member->load([
            "teams" => function ($query) {
                $query->orderBy("name", "asc");
            }
        ]);
        $memberTeamNames = $member->teams->map(fn($t) => $t->name)->toArray();
        $user = $request->user();
        $allTeams = Team::orderBy("name")->get()
            ->map(fn($t) => ["name" => $t->name, "id" => $t->id])
            ->filter(fn($t) => !in_array($t["name"], $memberTeamNames));
        if (!$user->isAdmin) {
            $allTeams = $allTeams->filter(fn($t) => Gate::allows('edit-team-details', $t["id"]));
        }
        // oh boy, this ->values() took me hours...
        // and in map() I can use $t->..., in filter() I must use $t["..."] ???
        $allTeams = $allTeams->values();
        $memberRoles = MemberRole::all()->map(fn($r) => ["title" => $r->title, "id" => $r->id]);
        foreach ($member->teams as $team) {
            $team->team_member->member_role_title = $team->team_member->member_role->title;
        }
        return inertia(
            'Member/Show',
            [
                "member" => $member,
                "teamToMemberDialogShown" => true,
                "teamIndex" => $teamIndex,
                "allTeams" => $allTeams,
                "memberRoles" => $memberRoles,
            ]
        );
    }

    public function indexWithHistory(Member $member, Request $request)
    {
        if (!Gate::allows('readhistory')) {
            abort(403);
        }
        $id = $request->all()["id"]; // TODO? get?
        $history = HistoryController::getByTableAndId("members", $id);
        $users = User::all()->map(fn($u) => ["email" => $u->email, "id" => $u->id]);
        $teams = Team::all()->map(fn($t) => ["name" => $t->name, "id" => $t->id]);
        // $members = Member::all()->map(fn($m) => ["name" => $m->last_name . ", " . $m->first_name, "id" => $m->id]);
        $members = [$member];

        return inertia(
            'Member/Index',
            [
                "members" => $members,
                "teams" => $teams,
                "users" => $users,
                "history" => $history,
                "retour" => "member.index"
            ]
        );
    }




    public function teams(Request $request, int $member_id)
    {
        // this function is only called via fetch from exportExcel (like an api function)
        if (!Gate::allows('see-member-details', $member_id)) {
            return [];
        }
        $m = Member::find($member_id);
        $m->load([
            "teams" => function ($query) {
                $query->orderBy("name", "asc");
            }
        ]);
        return [
            "teams" => $m->teams->map(fn($team) => $team->name)
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
        $user = $request->user();
        if (!Gate::allows('see-member-details', $member->id)) {
            abort(403);
        }

        $all = $request->all();
        $v = $request->validate([
            "first_name" => "required",
            "last_name" => "required",
            "birthday" => "digits:4|nullable",
            'email_adfc' => 'email|nullable',
            'email_private' => 'email|nullable',
            'adfc_id' => "digits:8|nullable"
        ]);
        if (!$user->isAdmin) {
            $all = $this->removeFields($all);
        }
        $member->update($all);
        return redirect()->back()->with('success', "Mitgliedseintrag wurde geändert");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member, Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin) {
            abort(403);
        }
        $member->delete();
        return redirect()->back()->with('success', "Mitgliedseintrag wurde gelöscht");
    }

    private $tmHelper;
    public function __construct()
    {
        $this->tmHelper = new TeamMemberHelper(
            "Neuer AG/OG-Eintrag wurde erzeugt",
            "AG/OG-Eintrag wurde geändert",
            "AG/OG-Eintrag wurde gelöscht"
        );
    }

    public function updateTM(Request $request)
    {
        return $this->tmHelper->updateTM($request, "member");
    }

    public function storeTM(Request $request)
    {
        return $this->tmHelper->storeTM($request, "member");
    }

    public function destroyTM(Request $request, int $id)
    {
        return $this->tmHelper->destroyTM($request, $id);
    }

    function removeFields(array $params): array
    {
        unset($params["latest_first_aid_training"]);
        unset($params["next_first_aid_training"]);
        unset($params["responded_to_questionaire"]);
        unset($params["responded_to_questionaire_at"]);
        unset($params["dsgvo_signature"]);
        unset($params["police_certificate"]);
        unset($params["polcert_date"]);
        return $params;
    }
}
