<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalPokemonMap extends Model
{
    protected $fillable = [
        'pokemon_id',
        'source',
        'external_id',
        'external_name',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pokemon()
    {
        return $this->belongsTo(Pokemon::class);
    }
}