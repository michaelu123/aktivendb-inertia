<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class HistoryController extends Controller
{
    public function show()
    {
        if (!Gate::allows("readhistory")) {
            abort(403);
        }

        return inertia(
            'HistoryView',
            [
                "historyShown" => false
            ]
        );
    }


    public static function getByTableAndId(string $table, int $id)
    {
        $q1 = DB::table("history")->where('reference_id', "=", $id)->where('reference_table', "=", $table);
        $q2 = DB::table("history")->join("project_team_member", "history.reference_id", "=", "project_team_member.id")
            ->where("history.reference_table", "=", "project_team_member")->select("history.*");
        if ($table == 'members') {
            /*
            select h.* from history h
            where h.reference_id = 21 and h.reference_table = "members" 
            union
            select h.* from history h, project_team_member p 
            where p.member_id = 21 and p.id = h.reference_id and h.reference_table = "project_team_member"
            */
            $q2 = $q2->where("project_team_member.member_id", "=", $id);
        } elseif ($table == 'project_teams') {
            /*
                select h.* from history h
                where h.reference_id = 20 and h.reference_table = "project_teams" 
                union
                select h.* from history h, project_team_member p 
                where p.project_team_id = 20 and p.id = h.reference_id and h.reference_table = "project_team_member"
            */
            $q2 = $q2->where("project_team_member.project_team_id", "=", $id);
        } else {
            abort(404);
        }
        return $q1->union($q2)->orderBy("created_at", "asc")->get();
    }


    public function showWithHistory(Request $request)
    {
        if (!Gate::allows("readhistory")) {
            abort(403);
        }
        $v = $request->validate([
            "begin" => "date_format:Y-m-d|nullable",
            "end" => "date_format:Y-m-d|nullable",
            "id" => "int|nullable",
            "m_or_t" => "in:m,t|nullable",
        ]);

        if ($v["m_or_t"] == "m") {
            $history = $this->getByTableAndId("members", $v["id"]);
        } else if ($v["m_or_t"] == "t") {
            $history = $this->getByTableAndId("project_teams", $v["id"]);
        } else {
            $begin = $v["begin"];
            $end = $v["end"];
            $history = History::where("created_at", ">=", $begin)->where("created_at", "<=", $end)->get();
        }
        $users = User::all()->map(fn($u) => ["email" => $u->email, "id" => $u->id]);
        $teams = Team::all()->map(fn($t) => ["name" => $t->name, "id" => $t->id]);
        $members = Member::all()->map(fn($m) => ["name" => $m->last_name . ", " . $m->first_name, "id" => $m->id]);

        return inertia(
            'HistoryView',
            [
                "teams" => $teams,
                "members" => $members,
                "users" => $users,
                "history" => $history,
            ]
        );
    }

}
