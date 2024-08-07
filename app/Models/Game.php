<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PlayerGameStats;

class Game extends Model
{
    public function playerGameStats()
    {
        return $this->hasMany(PlayerGameStats::class);
    }
}
