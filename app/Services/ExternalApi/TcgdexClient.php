<?php

namespace App\Services\ExternalApi;

use TCGdex\TCGdex;

use TCGdex\Query;
use Illuminate\Support\Facades\RateLimiter;

class TcgdexClient
{
    protected TCGdex $sdk;

    public function __construct()
    {
        $this->sdk = new TCGdex("en");
    }

    private function throttle(): void
    {
        while (! RateLimiter::attempt('tcgdex', 20, fn () => true)) {
            usleep(250000); // 0.25s backoff
        }
    }

    public function findCardsByName(string $name): array
    {
        $this->throttle();

        $query = Query::create()
            ->contains('name', $name);

        $results = $this->sdk->card->list($query);

        return $results ?: [];
    }

    public function getCard(string $cardId)
    {
        $this->throttle();

        return $this->sdk->card->get($cardId);
    }

    public function getSet(string $setId)
    {
        $this->throttle();

        return $this->sdk->set->get($setId);
    }
}