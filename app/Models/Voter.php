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
        'image_path',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Get the ward that this voter belongs to.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
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
     * Scope to filter by ward.
     */
    public function scopeInWard($query, $wardId)
    {
        return $query->where('ward_id', $wardId);
    }

    /**
     * Scope to filter by status (voted/unvoted).
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
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
}
