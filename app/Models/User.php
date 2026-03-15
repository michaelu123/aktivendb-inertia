<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable;
    use SoftDeletes;
    use HasApiTokens;
    use Notifiable;

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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->mayReadHistory;
    }

    public function getFilamentName(): string
    {
        return $this->member->first_name . " " . $this->member->last_name;
    }
}

