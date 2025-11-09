<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoterCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function voters()
    {
        return $this->belongsToMany(Voter::class, 'voter_category_voter')
            ->withPivot('user_id')
            ->withTimestamps();
    }
}
