<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable;
    use SoftDeletes;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
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

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->has_ability("globale-schreibrechte")
        )->shouldCache();
    }
    protected function mayReadHistory(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->has_ability("readhistory")
        )->shouldCache();
    }


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

    public function has_ability(string $ability_ref, ?Team $team = null)
    {
        if ($team == null) {
            foreach ($this->abilities()->where('global', true)->get() as $user_ability) {
                if ($user_ability->reference == $ability_ref) {
                    return true;
                }
            }
        } else {
            $member = $this->member()->with('teams')->first();

            foreach ($member->teams()->get() as $user_team) {
                if ($team->id == $user_team->id) {
                    $ability = Ability::with('member_roles')->where('reference', $ability_ref)->first();

                    foreach ($ability->member_roles as $ability_member_role) {
                        if ($ability_member_role->id == $user_team->team_member->member_role_id) {
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
        return $this->belongsToMany('App\Models\Ability', 'ability_user')->whereNull('ability_user.deleted_at');
    }
}

