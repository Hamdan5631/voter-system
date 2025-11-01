<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ward_number',
        'panchayat_id',
        'description',
    ];

    /**
     * Get all users assigned to this ward.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the team lead for this ward.
     */
    public function teamLead()
    {
        return $this->hasOne(User::class)->where('role', 'team_lead');
    }

    /**
     * Get the booth agent for this ward.
     */
    public function boothAgent()
    {
        return $this->hasOne(User::class)->where('role', 'booth_agent');
    }

    /**
     * Get all workers for this ward.
     */
    public function workers()
    {
        return $this->hasMany(User::class)->where('role', 'worker');
    }

    /**
     * Get all voters in this ward.
     */
    public function voters()
    {
        return $this->hasMany(Voter::class);
    }

    /**
     * Get the panchayat that owns this ward.
     */
    public function panchayat()
    {
        return $this->belongsTo(Panchayat::class);
    }
}
