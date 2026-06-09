<?php

namespace App\Jobs;

use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ExternalApi\TcgdexClient;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(TcgdexClient $client): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $cardId = $pokemon->tcg_card_id ?? null;

        if (!$cardId) {
            return;
        }

        $card = $client->getCard($cardId);

        if (!$card) {
            $this->release(5);
            return;
        }

        $pokemon->update([
            'tcg_data' => json_encode($card),
        ]);
    }
}