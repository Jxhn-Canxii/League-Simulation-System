<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leagues extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_conference'
    ];

    public function teams()
    {
        return $this->hasMany(Teams::class);
    }
}
