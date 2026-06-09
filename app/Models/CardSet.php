<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'series',
        'logo_url',
        'symbol_url',
        'release_date',
        'card_count_official',
        'card_count_total',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'raw_data' => 'array',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}