<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberRole;
use App\Models\ProjectTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    private function customValidator(Request $request, $id = null)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "first_name" => "required",
                "last_name" => "required",
                "birthday" => "date|nullable",
                'email_adfc' => 'email|nullable',
                'email_private' => 'email|nullable'
            ],
            array(
                'required' => ':attribute muss befüllt werden.',
                'email' => ':attribute muss eine E-Mail sein',
                'unique' => ':attribute muss einzigartig sein'
            ),
            array(
                'email_adfc' => 'E-Mail (ADFC)',
                'email_private' => 'E-Mail (Privat)',
                'adfc_id' => 'Mitgliedsnummer',
                'first_name' => 'Vorname',
                'last_name' => 'Nachname',
                'birtday' => 'Geburtsjahr',
            )
        );

        return $validator;
    }

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
            ["member" => $member, "readonly" => false]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $all = $request->all();
        // $validator = $this->customValidator($request);
        // $fail = $validator->fails();
        // if (!$fail) {
        //     //            $all = $request->all();
        //     Member::create($all);
        // }
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
        $readonly = !!$request->query("readonly", true);
        $member->load(["project_teams"]);
        foreach ($member->project_teams as $project_team) {
            $project_team->project_team_member->member_role_title = $project_team->project_team_member->member_role->title;
        }
        return inertia(
            'Member/Show',
            ["member" => $member, "readonly" => $readonly]
        );
    }

    public function showWithDialog(Member $member, Request $request)
    {
        $readonlyM = !!$request->query("readonlyM", true);
        $readonlyT = !!$request->query("readonlyT", true);
        $teamIndex = $request->query("teamIndex");
        $member->load(["project_teams"]);
        $allProjectTeams = ProjectTeam::all()->map(fn($t) => ["name" => $t->name, "id" => $t->id]);
        $memberRoles = MemberRole::all()->map(fn($r) => ["title" => $r->title, "id" => $r->id]);
        foreach ($member->project_teams as $project_team) {
            $project_team->project_team_member->member_role_title = $project_team->project_team_member->member_role->title;
        }
        return inertia(
            'Member/Show',
            [
                "member" => $member,
                "readonly" => $readonlyM,
                "readonlyT" => $readonlyT,
                "teamToMemberDialogShown" => true,
                "teamIndex" => $teamIndex,
                "allProjectTeams" => $allProjectTeams,
                "memberRoles" => $memberRoles,
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
    public function destroy(Member $member)
    {
        //
    }
}
