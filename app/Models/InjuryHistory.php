<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InjuryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'injury_type',
        'recovery_games',
        'performance_impact',
        'injury_date',
        'recovery_date',
    ];
}
