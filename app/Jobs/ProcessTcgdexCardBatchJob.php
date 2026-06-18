<?php

namespace App\Jobs;

use App\Actions\Pokemon\SyncFromTcgdex;
use App\Models\Pokemon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessTcgdexCardBatchJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(
        public int $pokemonId,
        public array $cardIds
    ) {}

    public function handle(SyncFromTcgdex $action): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $cards = [];

        foreach ($this->cardIds as $id) {
            $card = app(\App\Services\ExternalApi\TcgdexClient::class)
                ->getCard($id);

            if (!$card) continue;

            // normalize
            $cards[] = json_decode(json_encode($card), true);
        }

        if (!empty($cards)) {
            $action->syncCards($pokemon, $cards);
        }
    }
}