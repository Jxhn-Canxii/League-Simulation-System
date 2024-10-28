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
        'contract_years',
        'contract_expires_at',
        'is_active',
        'is_rookie',
        'age',
        'retirement_age',
        'injury_prone_percentage',
        'role',
        'type',
        'shooting_rating',
        'defense_rating',
        'passing_rating',
        'rebounding_rating',
        'overall_rating',
        'draft_id',
        'draft_order',
        'drafted_team_id',
        'is_drafted',
        'draft_status',
    ];

    // The attributes that are hidden for arrays (optional)
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
