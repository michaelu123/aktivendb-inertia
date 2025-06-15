<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        Gate::define('administer-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('add-edit-member', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('add-edit-project-team', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('see-member-details', function (User $user, int $member_id) {
            if ($user->member_id == $member_id || $user->isAdmin()) {
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


            /*$member = Member::with('project_teams')->find($member_id);

            foreach($user->member()->with('project_teams')->first()->project_teams()->get() as $user_project_team)
            {
                foreach($member->project_teams()->get() as $member_project_team)
                {
                    if($user_project_team->id == $member_project_team->id)
                    {
                        if($user->has_ability('leserechte', $member_project_team))
                        {
                            return true;
                        }
                    }
                }
            }

            return false;*/
        });

        // TODO
        // Gate::define('see-project-team-details', function (User $user, int $project_team_id) {
        //     $project_team = ProjectTeam::find($project_team_id);

        //     return ($user->isAdmin() || $user->has_ability('leserechte', $project_team));
        // });

        // Gate::define('edit-member-details', function (User $user, int $member_id) {
        //     if ($user->member_id == $member_id || $user->isAdmin()) {
        //         return true;
        //     }

        //     $member = Member::with('project_teams')->find($member_id);

        //     foreach ($user->member()->with('project_teams')->first()->project_teams()->get() as $user_project_team) {
        //         foreach ($member->project_teams()->get() as $member_project_team) {
        //             if ($user_project_team->id == $member_project_team->id) {
        //                 if ($user->has_ability('schreibrechte', $member_project_team)) {
        //                     return true;
        //                 }
        //             }
        //         }
        //     }

        //     return false;
        // });

        // Gate::define('edit-project-team-details', function (User $user, int $project_team_id) {
        //     $project_team = ProjectTeam::find($project_team_id);

        //     return ($user->isAdmin() || $user->has_ability('schreibrechte', $project_team));
        // });

        Gate::define('readhistory', function (User $user) {
            return $user->has_ability('readhistory');
        });

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     return $request->auth;
        // });
    }
}

/*



select amr.member_role_id 
from ability_member_role amr, abilities a 
where amr.ability_id = a.id 
    and a.reference = \'leserechte\' 
    and amr.deleted_at is null and a.deleted_at is null

*/