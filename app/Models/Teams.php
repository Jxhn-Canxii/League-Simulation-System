<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'acronym',
        'league_id',
        'conference_id',
        'primary_color',
        'secondary_color'
    ];
    public function league()
    {
        return $this->belongsTo(Leagues::class, 'league_id');
    }

    public function players()
    {
        return $this->hasMany(Player::class, 'team_id');
    }
}
