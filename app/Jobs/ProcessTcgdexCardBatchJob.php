<?php

namespace App\Jobs;

use App\Actions\Pokemon\SyncFromTcgdex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class ProcessTcgdexCardBatchJob implements ShouldQueue
{
    use Queueable, Batchable;

    public int $timeout = 120;
    public int $tries = 3;

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