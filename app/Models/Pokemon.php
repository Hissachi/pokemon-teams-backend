<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    protected $table = 'pokemons';
    protected $fillable = [
        'team_id',
        'name',
        'type',
        'ability',
        'image'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}