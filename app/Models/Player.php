<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory;

    // Define the table associated with the model (optional if follows Laravel conventions)
    protected $table = 'players';

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'team_id',
        'age',
        'retirement_age',
        'injury_prone_percentage',
        'contract_years',
        'contract_expires_at',
        'is_active',
        'is_rookie',
        'role', // Add this line
    ];

    // The attributes that should be hidden for arrays (optional)
    protected $hidden = [
        // Any attributes you want to hide
    ];

    // The attributes that should be cast to native types (optional)
    protected $casts = [
        'contract_expires_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Teams::class);
    }

    public function playerGameStats()
    {
        return $this->hasMany(PlayerGameStats::class);
    }
}

