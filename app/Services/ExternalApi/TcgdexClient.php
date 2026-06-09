<?php

namespace App\Services\ExternalApi;

use TCGdex\TCGdex;
use Illuminate\Support\Facades\RateLimiter;

class TcgdexClient
{
    protected TCGdex $sdk;

    public function __construct()
    {
        $this->sdk = new TCGdex("en");
    }

    public function getCard(string $cardId)
    {
        return RateLimiter::attempt(
            'tcgdex',
            1,
            function () use ($cardId) {
                return $this->sdk->card->get($cardId);
            },
            2
        );
    }

    public function getSet(string $setId)
    {
        return RateLimiter::attempt(
            'tcgdex',
            1,
            function () use ($setId) {
                return $this->sdk->set->get($setId);
            },
            2
        );
    }
}