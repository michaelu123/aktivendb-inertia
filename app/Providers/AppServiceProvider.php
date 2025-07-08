<?php

namespace App\Providers;

use App\Listeners\HistoryListener;
use App\Models\Member;
use App\Models\Team;
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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(HistoryListener::class);

        Gate::define('administer-users', function (User $user) {
            return $user->isAdmin;
        });

        Gate::define('add-edit-member', function (User $user) {
            return $user->isAdmin;
        });

        Gate::define('add-edit-team', function (User $user) {
            return $user->isAdmin;
        });

        Gate::define('see-member-details', function (User $user, int $member_id) {
            if ($user->member_id == $member_id || $user->isAdmin) {
                return true;
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

            return $count->cnt > 0;


            /*$member = Member::with('teams')->find($member_id);

            foreach($user->member()->with('teams')->first()->teams()->get() as $user_team)
            {
                foreach($member->teams()->get() as $member_team)
                {
                    if($user_team->id == $member_team->id)
                    {
                        if($user->has_ability('leserechte', $member_team))
                        {
                            return true;
                        }
                    }
                }
            }

            return false;*/
        });

        Gate::define('see-team-details', function (User $user, int $team_id) {
            $team = Team::find($team_id);

            return ($user->isAdmin || $user->has_ability('leserechte', $team));
        });

        Gate::define('edit-member-details', function (User $user, int $member_id) {
            if ($user->member_id == $member_id || $user->isAdmin) {
                return true;
            }

            $member = Member::with('teams')->find($member_id);

            foreach ($user->member()->with('teams')->first()->teams()->get() as $user_team) {
                foreach ($member->teams()->get() as $member_team) {
                    if ($user_team->id == $member_team->id) {
                        if ($user->has_ability('schreibrechte', $member_team)) {
                            return true;
                        }
                    }
                }
            }

            return false;
        });

        Gate::define('edit-team-details', function (User $user, int $team_id) {
            $team = Team::find($team_id);

            return ($user->isAdmin || $user->has_ability('schreibrechte', $team));
        });

        Gate::define('readhistory', function (User $user) {
            return $user->has_ability('readhistory');
        });

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     return $request->auth;
        // });
    }
}
