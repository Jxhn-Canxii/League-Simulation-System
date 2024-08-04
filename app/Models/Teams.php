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
    ];
    public function league()
    {
        return $this->belongsTo(Leagues::class, 'league_id');
    }
}
