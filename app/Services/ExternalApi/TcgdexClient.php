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

    public function findCardByName(string $name)
{
    $this->throttle();

    $query = Query::create()
        ->contains('name', $name)
        ->paginate(1, 1); // IMPORTANT: 1 result only

    $result = $this->sdk->card->list($query);

    if (empty($result) || !isset($result[0])) {
        return null;
    }

    return $result[0];
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