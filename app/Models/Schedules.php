<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    use HasFactory;
    protected $fillable = [
        'game_id',
        'round',
        'season_id',
        'is_conference',
        'home_id',
        'home_score',
        'away_id',
        'away_score',
        'status',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(Teams::class, 'home_id');
    }

    // Define relationship with away team
    public function awayTeam()
    {
        return $this->belongsTo(Teams::class, 'away_id');
    }
}
