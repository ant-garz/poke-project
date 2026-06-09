<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardAttack extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'name',
        'damage',
        'effect',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'array',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}