<?php

namespace App\Jobs;

use App\Actions\Pokemon\SyncFromPokeApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchPokeApiDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pokemonId
    ) {}

    public function handle(SyncFromPokeApi $action): void
    {
        $action->execute($this->pokemonId);
    }
}