<?php

namespace App\Jobs;

use App\Actions\Pokemon\SyncFromTcgdex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(SyncFromTcgdex $action): void
    {
        $action->execute($this->pokemonId);
    }
}