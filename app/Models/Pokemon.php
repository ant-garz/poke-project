<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

        'primary_type_id',
        'secondary_type_id',

        'raw_pokeapi',
        'raw_tcgdex'
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
            'raw_pokeapi' => 'array',
            'raw_tcgdex' => 'array',
        ];
    }

    protected $appends = [
        'artwork_url',
    ];

    protected $hidden = [
        'raw_pokeapi',
        'raw_tcgdex',
    ];

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

    public function primaryType()
    {
        return $this->belongsTo(Type::class, 'primary_type_id');
    }

    public function secondaryType()
    {
        return $this->belongsTo(Type::class, 'secondary_type_id');
    }

    protected function types(): Attribute
    {
        return Attribute::make(
            get: fn () => collect([
                $this->primaryType,
                $this->secondaryType,
            ])->filter()->values()
        );
    }

    public function getPokedexNumberAttribute($value)
    {
        return $value;
    }

    public function getPaddedPokedexNumberAttribute(): ?string
    {
        if (!$this->pokedex_number) {
            return null;
        }

        return str_pad($this->pokedex_number, 4, '0', STR_PAD_LEFT);
    }

    public function cards()
    {
        return $this->morphMany(Card::class, 'cardable');
    }

    public function getArtworkUrlAttribute(): ?string
    {
        $value = $this->attributes['artwork_url'] ?? null;

        if (empty($value)) {
            return null;
        }

        return rtrim($value, '/') . '/low.webp';
    }
}