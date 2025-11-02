<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoterStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'voter_id',
        'user_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the voter that this status belongs to.
     */
    public function voter()
    {
        return $this->belongsTo(Voter::class);
    }

    /**
     * Get the user who updated the status.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
