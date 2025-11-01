<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panchayat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'district',
        'description',
    ];

    /**
     * Get all wards in this panchayat.
     */
    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
