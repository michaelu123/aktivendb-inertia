<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Member;
use App\Http\Controllers\Controller;

class MembersController extends Controller
{
    // public function all(Request $request)
    // {
    //     return Member::orderBy("last_name")->orderBy("first_name")->get();
    // }

    const MODEL = "App\Models\Member";
    use RESTActions;



    public function all(Request $request)
    {
        $members = Member::with(['user'])->get();
        foreach ($members as $m) {
            $m->apiCall = true;
        }
        return $members;
    }

    public function get(Request $request, $id)
    {
        $member = Member::with(['user', 'teams'])->find($id);
        foreach ($member->teams as $team) {
            $team->team_member->member_role_title = $team->team_member->member_role->title;
        }
        $member->with_details = true;
        $member->apiCall = true;
        return $member;
    }

    private function customValidator(Request $request, $id = null)
    {
        $validator = Validator::make(
            $request->all(),
            [
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
                'last_name' => 'Nachname'
            )
        );

        return $validator;
    }
}
