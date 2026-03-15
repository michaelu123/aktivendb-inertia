<?php

namespace App\Providers;

use App\Listeners\HistoryListener;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    // special code to not check permissions coming from Serienbrief form
    protected function isFromSerienbrief()
    {
        dd("xxxxxxxxx"); // TODO is this ever called?
        if (str_starts_with(request()->getPathInfo(), "/sb/")) {
            return true;
        }
        if (!str_starts_with(request()->getPathInfo(), "/update")) {
            return false;
        }
        $req = request()->all();
        if (isset($req["components"])) {
            $components = $req["components"];
            $snapshot = $components[0]["snapshot"];
            $snapshot = json_decode($snapshot);
            $name = $snapshot->memo->name;
        } else {
            $name = "";
        }
        return $name == 'pages::serienbrief';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(HistoryListener::class);

        Gate::define('see-member-details', function (User $user, int $member_id = 0) {
            if ($user->member_id == $member_id || $user->isAdmin || $this->isFromSerienbrief()) {
                return true;
            }
            static $last_member_id = 0;
            static $last_result = false;
            if ($member_id == $last_member_id) {
                return $last_result;
            }

            $count = DB::table('project_team_member AS ptm1')
                ->join('project_team_member AS ptm2', 'ptm1.project_team_id', '=', 'ptm2.project_team_id')
                ->join('project_teams AS pt', 'ptm1.project_team_id', '=', 'pt.id')
                ->selectRaw('count(*) AS cnt')
                // ->whereRaw('ptm1.member_role_id in ( select amr.member_role_id from ability_member_role amr, abilities a where amr.ability_id = a.id and a.reference = \'leserechte\' and amr.deleted_at is null and a.deleted_at is null )')
                ->where('ptm1.member_role_id', "=", 1)
                ->where('ptm1.member_id', $user->member_id)
                ->where('ptm2.member_id', $member_id)
                ->whereNull('ptm1.deleted_at')
                ->whereNUll('ptm2.deleted_at')
                ->whereNUll('pt.deleted_at')
                ->first();

            $last_member_id = $member_id;
            $last_result = $count->cnt > 0;

            return $last_result;
        });

        Gate::define('edit-team-details', function (User $user, int $team_id) {
            if ($user->isAdmin) {
                return true;
            }

            static $last_team_id = 0;
            static $last_result = false;
            if ($team_id == $last_team_id) {
                return $last_result;
            }

            $count = DB::table('project_team_member AS ptm1')
                ->join('project_teams AS pt', 'ptm1.project_team_id', '=', 'pt.id')
                ->selectRaw('count(*) AS cnt')
                // ->whereRaw('ptm1.member_role_id in ( select amr.member_role_id from ability_member_role amr, abilities a where amr.ability_id = a.id and a.reference = \'leserechte\' and amr.deleted_at is null and a.deleted_at is null )')
                ->where('ptm1.member_role_id', "=", 1)
                ->where('ptm1.member_id', $user->member_id)
                ->where('ptm1.project_team_id', $team_id)
                ->whereNull('ptm1.deleted_at')
                ->whereNUll('pt.deleted_at')
                ->first();

            $last_team_id = $team_id;
            $last_result = $count->cnt > 0;
            return $last_result;
        });

        Gate::define('readhistory', function (User $user) {
            return $user->has_ability('readhistory');
        });
    }
}
