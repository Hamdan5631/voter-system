<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'booth_number',
        'panchayat_id',
        'ward_id',
    ];

    public function panchayat()
    {
        return $this->belongsTo(Panchayat::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
