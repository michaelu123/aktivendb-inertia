<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberRole;
use App\Models\Team;
use App\Models\TeamMember;
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
        $sess = $request->session();

        $store = $sess->get("store", []);
        $pageno = $request->query("pageno", null);
        if ($pageno !== null) {
            $store["pageno"] = $pageno;
        }
        $sess->put("store", $store);

        return inertia(
            'Member/Index',
            [
                "members" => Member::orderBy("last_name")->orderBy("first_name")->orderBy("first_name")->get(),
                "storeC" => $store
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
        $sess = $request->session();
        $store = $sess->get("store", []);

        $member = new Member;
        $member->id = -1;
        return inertia(
            'Member/Show',
            [
                "member" => $member,
                "storeC" => $store,
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
        if (!Gate::allows('see-member-details', $member->id)) {
            abort(403);
        }
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

        $member->load(["teams"]);
        foreach ($member->teams as $team) {
            $team->team_member->member_role_title = MemberRole::roleName($team->team_member->member_role_id);
        }
        return inertia(
            'Member/Show',
            [
                "member" => $member,
                "storeC" => $store
            ]
        );
    }

    public function showWithDialog(Member $member, Request $request)
    {
        if (!Gate::allows('see-member-details', $member->id)) {
            abort(403);
        }
        $sess = $request->session();

        $store = $sess->get("store", []);
        $readonly = !!$request->query("readonly", true);
        $store["readonly2"] = $readonly;
        $sess->put("store", $store);

        $teamIndex = $request->query("teamIndex");
        $member->load(["teams"]);
        $memberTeamNames = $member->teams->map(fn($t) => $t->name)->toArray();
        $allTeams = Team::orderBy("name")->get()
            ->map(fn($t) => ["name" => $t->name, "id" => $t->id])
            // oh boy, this ->values() took me hours...
            ->filter(fn($t) => !in_array($t["name"], $memberTeamNames))->values();
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
                "storeC" => $store
            ]
        );
    }

    public function indexWithHistory(Member $member, Request $request)
    {
        if (!Gate::allows('readhistory')) {
            abort(403);
        }
        $sess = $request->session();
        $store = $sess->get("store", []);
        $pageno = $request->query("pageno", null);
        if ($pageno !== null) {
            $store["pageno"] = $pageno;
        }
        $sess->put("store", $store);
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
                "storeC" => $store,
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
        $m->load(["teams"]);
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
        if (!$user->isAdmin && $user->id != $member->id) {
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
        $member->update($all);
        return redirect()->route("member.index")->with('success', "Mitgliedseintrag wurde geändert");
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
        return $this->tmHelper->updateTM($request);
    }

    public function storeTM(Request $request)
    {
        return $this->tmHelper->storeTM($request);
    }

    public function destroyTM(Request $request, int $id)
    {
        return $this->tmHelper->destroyTM($request, $id);
    }
}
