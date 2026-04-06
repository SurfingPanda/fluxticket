<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'employee_id',
        'job_title',
        'primary_contact',
        'secondary_contact',
        'department',
        'role',
        'email',
        'password',
        'profile_photo',
        'page_access',
        'routing_depts',
        'is_active',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

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
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'page_access'       => 'array',
            'routing_depts'     => 'array',
            'is_active'         => 'boolean',
        ];
    }

    /** Effective page access: per-user override merged over global defaults. */
    public function effectivePageAccess(): array
    {
        $global = SystemSetting::agentPageAccess();
        if ($this->page_access !== null) {
            return array_merge($global, $this->page_access);
        }
        return $global;
    }

    /** Effective routing departments: per-user override or global default. */
    public function effectiveRoutingDepts(): array
    {
        if ($this->routing_depts !== null) {
            return $this->routing_depts;
        }
        return SystemSetting::agentRoutingDepts();
    }
}
