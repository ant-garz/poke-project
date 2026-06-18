<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '@inertiajs/svelte';

    import Card from '@/components/ui/card/Card.svelte';
    import CardHeader from '@/components/ui/card/CardHeader.svelte';
    import CardTitle from '@/components/ui/card/CardTitle.svelte';
    import CardDescription from '@/components/ui/card/CardDescription.svelte';
    import CardContent from '@/components/ui/card/CardContent.svelte';
    import Button from '@/components/ui/button/Button.svelte';
    import Badge from '@/components/ui/badge/Badge.svelte';
    import Separator from '@/components/ui/separator/Separator.svelte';
    import Skeleton from '@/components/ui/skeleton/Skeleton.svelte';
    import JsonTree from '@/components/JsonTree.svelte';

    import { formatHeight, formatWeight } from '@/lib/pokemon-units';

    let pokemon = $state<any>(null);
    let loading = $state(true);

    let showCards = $state(true);
    let showRaw = $state(false);

    const height = $derived(pokemon ? formatHeight(pokemon.height) : null);
    const weight = $derived(pokemon ? formatWeight(pokemon.weight) : null);

    const pokemonId = page.url.split('/').pop();

    onMount(async () => {
        try {
            const response = await fetch(`/api/v1/public/pokemon/${pokemonId}`);
            pokemon = await response.json();
        } finally {
            loading = false;
        }
    });

    const statColor = (value: number) => {
        if (value >= 120) return 'bg-green-500';
        if (value >= 80) return 'bg-blue-500';
        if (value >= 50) return 'bg-yellow-500';
        return 'bg-red-500';
    };

    let audioEl;

    function playAudio() {
        audioEl?.play().catch(console.error);
    }

    function formatMoney(value: number | null | undefined, unit: string) {
        if (value === null || value === undefined) return '—';

        const symbol =
            unit === 'USD' ? '$'
            : unit === 'EUR' ? '€'
            : unit || '';

        return `${symbol}${value}`;
    }
</script>

<svelte:head>
    <title>
        {pokemon ? `${pokemon.name} | Pokémon` : 'Loading Pokémon'}
    </title>
</svelte:head>

<div class="container mx-auto space-y-6 p-6">

{#if loading}

    <div class="space-y-4">
        <Skeleton class="h-64 w-full" />
        <Skeleton class="h-48 w-full" />
        <Skeleton class="h-96 w-full" />
    </div>

{:else if pokemon}

<!-- HERO -->
<Card>
    <CardContent class="p-6">
        <div class="flex flex-col gap-6 md:flex-row">

            <div class="flex justify-center md:w-72">
                {#if pokemon?.pokeapi_artwork_url}
                    <img
                        src={pokemon.pokeapi_artwork_url}
                        alt={pokemon.name}
                        class="max-h-72 rounded-lg"
                        loading="lazy"
                    />
                {/if}
            </div>

            <div class="flex-1">
                <h1 class="text-4xl font-bold">
                    #{pokemon.pokedex_number} {pokemon.name}
                </h1>

                <div class="mt-2 flex flex-wrap gap-2">
                    {#if pokemon.primary_type}
                        <Badge
                            style={`background-color:${pokemon.primary_type.color};color:${pokemon.primary_type.text_color}`}
                        >
                            {pokemon.primary_type.name}
                        </Badge>
                    {/if}

                    {#if pokemon.secondary_type}
                        <Badge
                            style={`background-color:${pokemon.secondary_type.color};color:${pokemon.secondary_type.text_color}`}
                        >
                            {pokemon.secondary_type.name}
                        </Badge>
                    {/if}
                </div>

                <Separator class="my-4" />

                <Button onclick={playAudio} class="mt-2">
                    Play Audio
                </Button>

                <audio bind:this={audioEl} src={pokemon.cry_url}></audio>

                <Separator class="my-4" />

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <div class="text-sm text-muted-foreground">Height</div>
                        <div>
                            {#if height}
                                {height.meters} ({height.feet})
                            {/if}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-muted-foreground">Weight</div>
                        <div>
                            {#if weight}
                                {weight.kg} ({weight.lbs})
                            {/if}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-muted-foreground">Default</div>
                        <div>{pokemon.is_default ? 'Yes' : 'No'}</div>
                    </div>
                </div>
            </div>

        </div>
    </CardContent>
</Card>

<!-- BASE STATS -->
<Card>
    <CardHeader>
        <CardTitle>Base Stats</CardTitle>
    </CardHeader>

    <CardContent>
        <div class="space-y-4">
            {#each [
                ['HP', pokemon.hp],
                ['Attack', pokemon.attack],
                ['Defense', pokemon.defense],
                ['Sp. Atk', pokemon.special_attack],
                ['Sp. Def', pokemon.special_defense],
                ['Speed', pokemon.speed]
            ] as [label, value]}

                <div>
                    <div class="flex justify-between">
                        <span>{label}</span>
                        <span>{value}</span>
                    </div>

                    <div class="h-3 bg-muted rounded">
                        <div
                            class={`h-full ${statColor(value)}`}
                            style={`width:${Math.min(value, 255) / 255 * 100}%`}
                        />
                    </div>
                </div>

            {/each}
        </div>
    </CardContent>
</Card>

<!-- DETAILS -->
<Card>
    <CardHeader>
        <CardTitle>Details</CardTitle>
    </CardHeader>

    <CardContent>
        <div class="grid gap-4 md:grid-cols-2">
            <div>Slug: {pokemon.slug}</div>
            <div>Pokédex #: {pokemon.pokedex_number}</div>
            <div>Base XP: {pokemon.base_experience}</div>
            <div>Imported: {new Date(pokemon.source_csv_imported_at).toLocaleString()}</div>
            <div>TCGdex Sync: {new Date(pokemon.source_tcgdex_synced_at).toLocaleString()}</div>
            <div>PokéAPI Sync: {new Date(pokemon.source_pokeapi_synced_at).toLocaleString()}</div>
        </div>
    </CardContent>
</Card>

<!-- CARDS (COLLAPSIBLE) -->
<Card>
    <CardHeader>
        <button
            class="text-left w-full"
            onclick={() => showCards = !showCards}
        >
            <CardTitle>
                Cards ({pokemon.cards.length})
                <span class="text-sm text-muted-foreground ml-2">
                    {showCards ? '▲' : '▼'}
                </span>
            </CardTitle>
        </button>
    </CardHeader>

    {#if showCards}
        <CardContent>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                {#each pokemon.cards as card}
                    <Card>
                        <CardContent class="p-4">

                            {#if card.image_url}
                                <img
                                    src={`${card.image_url}/high.webp`}
                                    alt={card.name}
                                    class="mb-4 rounded-lg"
                                    loading="lazy"
                                />
                            {:else}
                                <div class="h-48 mb-4 bg-muted rounded-lg flex items-center justify-center">
                                    No image
                                </div>
                            {/if}

                            <h3 class="font-semibold">{card.name}</h3>

                            <!-- SET -->
                            {#if card.set}
                                <Separator class="my-4" />

                                <div class="space-y-3">
                                    <h4 class="font-medium">Set</h4>

                                    <div class="flex items-center gap-3">
                                        {#if card.set.logo_url}
                                            <img
                                                src={`${card.set.logo_url}.webp`}
                                                alt={card.set.name}
                                                loading="lazy"
                                                class="h-10 w-auto"
                                            />
                                        {/if}

                                        <div>
                                            <div class="font-semibold">
                                                {card.set.name}
                                            </div>

                                            {#if card.set.external_id}
                                                <div class="text-xs text-muted-foreground">
                                                    Set ID: {card.set.external_id}
                                                </div>
                                            {/if}
                                        </div>
                                    </div>

                                    {#if card.set.symbol_url}
                                        <div class="mt-2 flex items-center gap-2 text-sm text-muted-foreground">
                                            <span>Symbol:</span>

                                            <img
                                                src={`${card.set.symbol_url}.webp`}
                                                alt={`${card.set.name} symbol`}
                                                loading="lazy"
                                                class="h-5 w-5"
                                                onerror={(e) => {
                                                    e.currentTarget.src = `${card.set.symbol_url}.png`;
                                                }}
                                            />
                                        </div>
                                    {/if}
                                </div>
                            {/if}

                            {#if card.raw_data?.pricing}
                                <Separator class="my-4" />

                                <div class="space-y-3">
                                <h4 class="font-medium">Pricing</h4>

                                <!-- TCGPLAYER -->
                                {#if card.raw_data.pricing.tcgplayer}
                                <div class="rounded border p-3 text-sm space-y-2">
                                <div class="font-semibold">
                                TCGPlayer ({card.raw_data.pricing.tcgplayer.unit})
                                </div>

                                {#if card.raw_data.pricing.tcgplayer.normal}
                                <div class="space-y-1 text-muted-foreground">
                                <div class="font-medium text-foreground">Normal</div>
                                <div>Low: {formatMoney(card.raw_data.pricing.tcgplayer.normal.lowPrice, 'USD')}</div>
                                <div>Mid: {formatMoney(card.raw_data.pricing.tcgplayer.normal.midPrice, 'USD')}</div>
                                <div>High: {formatMoney(card.raw_data.pricing.tcgplayer.normal.highPrice, 'USD')}</div>
                                <div>Market: {formatMoney(card.raw_data.pricing.tcgplayer.normal.marketPrice, 'USD')}</div>
                                <div>Direct Low: {formatMoney(card.raw_data.pricing.tcgplayer.normal.directLowPrice, 'USD')}</div>
                                </div>
                                {/if}

                                {#if card.raw_data.pricing.tcgplayer['reverse-holofoil']}
                                <div class="space-y-1 text-muted-foreground mt-2">
                                <div class="font-medium text-foreground">Reverse Holofoil</div>
                                <div>Low: {formatMoney(card.raw_data.pricing.tcgplayer['reverse-holofoil'].lowPrice, 'USD')}</div>
                                <div>Mid: {formatMoney(card.raw_data.pricing.tcgplayer['reverse-holofoil'].midPrice, 'USD')}</div>
                                <div>High: {formatMoney(card.raw_data.pricing.tcgplayer['reverse-holofoil'].highPrice, 'USD')}</div>
                                <div>Market: {formatMoney(card.raw_data.pricing.tcgplayer['reverse-holofoil'].marketPrice, 'USD')}</div>
                                <div>Direct Low: {formatMoney(card.raw_data.pricing.tcgplayer['reverse-holofoil'].directLowPrice, 'USD')}</div>
                                </div>
                                {/if}

                                <div class="text-xs text-muted-foreground mt-2">
                                Updated: {card.raw_data.pricing.tcgplayer.updated}
                                </div>
                                </div>
                                {/if}

                                <!-- CARDMARKET -->
                                {#if card.raw_data.pricing.cardmarket}
                                <div class="rounded border p-3 text-sm space-y-2">
                                <div class="font-semibold">
                                CardMarket ({card.raw_data.pricing.cardmarket.unit})
                                </div>

                                <div class="space-y-1 text-muted-foreground">
                                <div class="font-medium text-foreground">Normal</div>

                                <div>Avg: {formatMoney(card.raw_data.pricing.cardmarket.avg, 'EUR')}</div>
                                <div>Low: {formatMoney(card.raw_data.pricing.cardmarket.low, 'EUR')}</div>
                                <div>Trend: {formatMoney(card.raw_data.pricing.cardmarket.trend, 'EUR')}</div>

                                <div>Avg 1: {formatMoney(card.raw_data.pricing.cardmarket.avg1, 'EUR')}</div>
                                <div>Avg 7: {formatMoney(card.raw_data.pricing.cardmarket.avg7, 'EUR')}</div>
                                <div>Avg 30: {formatMoney(card.raw_data.pricing.cardmarket.avg30, 'EUR')}</div>
                                </div>

                                <div class="space-y-1 text-muted-foreground mt-2">
                                <div class="font-medium text-foreground">Holo</div>

                                <div>Avg: {formatMoney(card.raw_data.pricing.cardmarket['avg-holo'], 'EUR')}</div>
                                <div>Low: {formatMoney(card.raw_data.pricing.cardmarket['low-holo'], 'EUR')}</div>
                                <div>Trend: {formatMoney(card.raw_data.pricing.cardmarket['trend-holo'], 'EUR')}</div>

                                <div>Avg 1: {formatMoney(card.raw_data.pricing.cardmarket['avg1-holo'], 'EUR')}</div>
                                <div>Avg 7: {formatMoney(card.raw_data.pricing.cardmarket['avg7-holo'], 'EUR')}</div>
                                <div>Avg 30: {formatMoney(card.raw_data.pricing.cardmarket['avg30-holo'], 'EUR')}</div>
                                </div>

                                <div class="text-xs text-muted-foreground mt-2">
                                Updated: {card.raw_data.pricing.cardmarket.updated}
                                </div>
                                </div>
                                {/if}
                                </div>
                            {/if}

                        </CardContent>
                    </Card>
                {/each}

            </div>
        </CardContent>
    {/if}
</Card>

<!-- RAW DATA (NEW SECTION) -->
<Card>
    <CardHeader>
        <CardTitle>Raw Data</CardTitle>
        <CardDescription>Developer / debugging view</CardDescription>
    </CardHeader>

    <CardContent>
        <Button onclick={() => showRaw = !showRaw}>
            {showRaw ? 'Hide' : 'Show'} Raw JSON
        </Button>

         {#if showRaw}
            <div class="mt-4 space-y-6">
                <JsonTree label="pokeapi" value={pokemon.raw_pokeapi} />
                <JsonTree label="tcgdex" value={pokemon.raw_tcgdex} />
            </div>
        {/if}
    </CardContent>
</Card>

{/if}

</div>