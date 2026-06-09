<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_set_id',
        'supertype',
        'cardable_id',
        'cardable_type',
        'external_id',
        'source_tcgdex_id',
        'name',
        'number',
        'hp',
        'rarity',
        'image_url',
        'raw_data',
    ];

    protected $casts = [
        'subtypes' => 'array',
    ];

    public function cardable(): MorphTo
    {
        return $this->morphTo();
    }

    public function set()
    {
        return $this->belongsTo(CardSet::class, 'card_set_id');
    }

    public function attacks()
    {
        return $this->hasMany(CardAttack::class);
    }

    public function isPokemon(): bool
    {
        return $this->supertype === 'Pokémon';
    }
}