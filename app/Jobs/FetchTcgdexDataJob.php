
<?php
use App\Services\ExternalApi\TcgdexClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;

class FetchTcgdexDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pokemonId) {}

    public function handle(TcgdexClient $tcg): void
    {
        $pokemon = Pokemon::findOrFail($this->pokemonId);

        $card = $tcg->findCardByName($pokemon->name);

        if (!$card) {
            logger()->warning('TCG card not found', [
                'pokemon_id' => $pokemon->id,
                'name' => $pokemon->name,
            ]);
            return;
        }

        $pokemon->update([
            'description' => $card->description ?? null,
            'artwork_url' => $card->image ?? null,
            'raw_tcgdex' => $card,
            'source_tcgdex_synced_at' => now(),
        ]);

        if ($pokemon->source_pokeapi_synced_at) {
            DownloadPokemonAssetsJob::dispatch($pokemon->id)
                ->onQueue('assets');
        }
    }
}