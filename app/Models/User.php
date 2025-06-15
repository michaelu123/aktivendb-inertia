<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Cast\Bool_;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_admin',
        'member_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isAdmin()
    {
        return !!$this->is_admin;
    }

    public function has_ability(string $ability_ref, ?ProjectTeam $project_team = null)
    {
        if ($project_team == null) {
            foreach ($this->abilities()->where('global', true)->get() as $user_ability) {
                if ($user_ability->reference == $ability_ref) {
                    return true;
                }
            }
        } else {
            $member = $this->member()->with('project_teams')->first();

            foreach ($member->project_teams()->get() as $user_project_team) {
                if ($project_team->id == $user_project_team->id) {
                    $ability = Ability::with('member_roles')->where('reference', $ability_ref)->first();

                    foreach ($ability->member_roles as $ability_member_role) {
                        if ($ability_member_role->id == $user_project_team->project_team_member->member_role_id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function abilities()
    {
        return $this->belongsToMany('App\Ability', 'ability_user')->whereNull('ability_user.deleted_at');
    }
}

