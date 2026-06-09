<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pokemon extends Model
{
    use HasFactory;

    protected $table = 'pokemon';

    protected $fillable = [
        'pokedex_number',
        'name',
        'slug',

        'is_default',
        'base_pokemon_id',

        'hp',
        'attack',
        'defense',
        'special_attack',
        'special_defense',
        'speed',

        'height',
        'weight',
        'base_experience',

        'description',

        'sprite_url',
        'artwork_url',
        'cry_url',

        'source_csv_imported_at',
        'source_pokeapi_synced_at',
        'source_tcgdex_synced_at',
    ];

    protected static function booted()
    {
        static::creating(function ($pokemon) {
            if (!$pokemon->slug) {
                $pokemon->slug = \Str::slug($pokemon->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',

            'source_csv_imported_at' => 'datetime',
            'source_pokeapi_synced_at' => 'datetime',
            'source_tcgdex_synced_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // later on if desired can support variant pokemon
    // example
    /**
     * Charizard
     *  ├─ Mega Charizard X
     *  ├─ Mega Charizard Y
     *  ├─ Gigantamax Charizard
     */
    public function basePokemon(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_pokemon_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_pokemon_id');
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class, 'pokemon_type')
            ->withPivot('slot')
            ->orderBy('pokemon_type.slot');
    }
}