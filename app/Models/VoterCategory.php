<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoterCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voters()
    {
        return $this->belongsToMany(Voter::class, 'voter_category_voter')
            ->withPivot('user_id')
            ->withTimestamps();
    }
}
