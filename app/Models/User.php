<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'ward_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the ward that the user belongs to.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Get all voters assigned to this worker.
     */
    public function assignedVoters()
    {
        return $this->belongsToMany(Voter::class, 'voter_worker_assignments', 'worker_id', 'voter_id')
            ->withPivot('assigned_by', 'remark')
            ->withTimestamps();
    }

    /**
     * Get all voter assignments made by this team lead.
     */
    public function assignments()
    {
        return $this->hasMany(VoterWorkerAssignment::class, 'assigned_by');
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user is team lead.
     */
    public function isTeamLead(): bool
    {
        return $this->hasRole('team_lead');
    }

    /**
     * Check if user is booth agent.
     */
    public function isBoothAgent(): bool
    {
        return $this->hasRole('booth_agent');
    }

    /**
     * Check if user is worker.
     */
    public function isWorker(): bool
    {
        return $this->hasRole('worker');
    }
}
