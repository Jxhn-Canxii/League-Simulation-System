<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Player;
use App\Models\Game;

class PlayerGameStats extends Model
{
    protected $fillable = [
        'player_id',
        'season_id',
        'game_id',
        'team_id',
        'minutes',
        'points',
        'rebounds',
        'assists',
        'steals',
        'blocks',
        'turnovers',
        'fouls',
        '3PM',
        '2PM',
        'FTM',
        '3PM_attempts',
        '2PM_attempts',
        'FT_attempts'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
