<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seasons extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'league_id',
        'type',
        'match_type',
        'start_playoffs',
        'is_conference',
        'status',
        'finals_winner_id',
        'finals_winner_name',
        'finals_winner_score',
        'finals_loser_id',
        'finals_loser_name',
        'finals_loser_score',
        'champion_id',
        'champion_name',
        'weakest_id',
        'weakest_name',
        'created_at',
        'updated_at',
    ];

    // Season.php
        public static function latestSeason()
        {
            return self::orderBy('id', 'desc')->first();
        }

}
