<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Voter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_number',
        'ward_id',
        'panchayat',
        'panchayat_id',
        'image_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Status is now stored in voter_statuses table
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
        'status',
        'status_updated_by',
        'status_history',
    ];

    /**
     * Get the ward that this voter belongs to.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Get the panchayat that this voter belongs to.
     */
    public function panchayat()
    {
        return $this->belongsTo(Panchayat::class);
    }

    /**
     * Get the worker assigned to this voter.
     */
    public function worker()
    {
        return $this->belongsToMany(User::class, 'voter_worker_assignments', 'voter_id', 'worker_id')
            ->withPivot('assigned_by', 'remark')
            ->withTimestamps();
    }

    /**
     * Get the assignment record for this voter.
     */
    public function assignment()
    {
        return $this->hasOne(VoterWorkerAssignment::class);
    }

    /**
     * Get all status history for this voter.
     */
    public function voterStatuses()
    {
        return $this->hasMany(VoterStatus::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest status record for this voter.
     */
    public function latestStatus()
    {
        return $this->hasOne(VoterStatus::class)->latestOfMany();
    }

    /**
     * Get status updater through latest status relationship.
     */
    public function getStatusUpdaterUser()
    {
        $latestStatus = $this->latestStatus;
        return $latestStatus ? $latestStatus->user : null;
    }

    /**
     * Scope to filter by ward.
     */
    public function scopeInWard($query, $wardId)
    {
        return $query->where('ward_id', $wardId);
    }

    /**
     * Scope to filter by status (not_voted, voted, visited).
     */
    public function scopeStatus($query, $status)
    {
        return $query->whereHas('latestStatus', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    /**
     * Get the current status attribute (from latest voter_statuses record).
     */
    public function getStatusAttribute()
    {
        $latestStatus = $this->latestStatus;
        return $latestStatus ? $latestStatus->status : 'not_voted';
    }

    /**
     * Get the status_updated_by attribute (user who last updated status).
     */
    public function getStatusUpdatedByAttribute()
    {
        $latestStatus = $this->latestStatus;
        return $latestStatus ? $latestStatus->user_id : null;
    }

    /**
     * Get status updater relationship (returns user from latest status).
     */
    public function statusUpdater()
    {
        $latestStatus = $this->latestStatus;
        return $latestStatus ? $latestStatus->user : null;
    }

    /**
     * Get valid status values.
     */
    public static function getValidStatuses(): array
    {
        return ['not_voted', 'voted', 'visited'];
    }

    /**
     * Scope to search by serial number.
     */
    public function scopeSearchSerialNumber($query, $serialNumber)
    {
        return $query->where('serial_number', 'like', "%{$serialNumber}%");
    }

    /**
     * Scope to filter by panchayat.
     */
    public function scopePanchayat($query, $panchayat)
    {
        return $query->where('panchayat', 'like', "%{$panchayat}%");
    }


    /**
     * Get the full URL for the voter image.
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image_path)) {
            return null;
        }

        $disk = env('VOTER_IMAGE_DISK', env('FILESYSTEM_DISK', 'public'));
        
        if ($disk === 's3') {
            return Storage::disk('s3')->url($this->image_path);
        } else {
            return Storage::disk('public')->url($this->image_path);
        }
    }

    /**
     * Get the status history for the voter.
     */
    public function getStatusHistoryAttribute()
    {
        return $this->voterStatuses()->with('user:id,name')->get();
    }
}
