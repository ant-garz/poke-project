<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'pokemon_id',
        'card_set_id',
        'external_id',
        'name',
        'supertype',
        'subtypes',
        'rarity',
        'number',
        'image_url',
        'hp',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'subtypes' => 'array',
            'raw_data' => 'array',
        ];
    }

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(CardSet::class, 'card_set_id');
    }

    public function attacks(): HasMany
    {
        return $this->hasMany(CardAttack::class);
    }
}