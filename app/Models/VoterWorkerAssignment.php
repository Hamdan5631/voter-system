<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoterWorkerAssignment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'voter_worker_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'voter_id',
        'worker_id',
        'assigned_by',
        'remark',
    ];

    /**
     * Get the voter for this assignment.
     */
    public function voter()
    {
        return $this->belongsTo(Voter::class);
    }

    /**
     * Get the worker for this assignment.
     */
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * Get the team lead who made this assignment.
     */
    public function teamLead()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
