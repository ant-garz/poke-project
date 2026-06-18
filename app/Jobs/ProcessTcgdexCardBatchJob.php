<?php

namespace App\Jobs;

use App\Actions\Pokemon\SyncFromTcgdex;
use App\Services\ExternalApi\TcgdexClient;
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
        $action->syncCards(
            $this->pokemonId,
            $this->cardIds
        );
    }
}