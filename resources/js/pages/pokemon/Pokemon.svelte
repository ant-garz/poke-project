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

    let pokemon = $state<any>(null);
    let loading = $state(true);
    let showRawJson = $state(false);

    const pokemonId = page.url.split('/').pop();

    onMount(async () => {
        try {
            const response = await fetch(
                `/api/v1/public/pokemon/${pokemonId}`
            );

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
		audioEl.play().catch(error => {
			console.error("Playback failed:", error);
		});
	}

    function formatMoney(value: number | null | undefined, unit: string) {
        if (value === null || value === undefined) return '—';

        const symbol = unit === 'USD'
            ? '$'
            : unit === 'EUR'
                ? '€'
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

            <!-- Hero -->

            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col gap-6 md:flex-row">
                        <div class="flex justify-center md:w-72">
                            <img
                                src={pokemon.pokeapi_artwork_url}
                                alt={pokemon.name}
                                class="max-h-72 rounded-lg"
                            />
                        </div>

                        <div class="flex-1">
                            <h1 class="text-4xl font-bold">
                                #{pokemon.pokedex_number}
                                {pokemon.name}
                            </h1>

                            <p class="mt-2 text-muted-foreground">
                                Base Experience:
                                {pokemon.base_experience}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
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

                            <Button onclick={playAudio} class="flex gap-2 mt-4 flex-wrap">
                                Play Audio
                            </Button>

                            <audio bind:this={audioEl} src={pokemon.cry_url}></audio>

                            <Separator class="my-4" />

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <div class="text-sm text-muted-foreground">
                                        Height
                                    </div>
                                    <div class="text-lg font-semibold">
                                        {pokemon.height}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm text-muted-foreground">
                                        Weight
                                    </div>
                                    <div class="text-lg font-semibold">
                                        {pokemon.weight}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm text-muted-foreground">
                                        Default Form
                                    </div>
                                    <div class="text-lg font-semibold">
                                        {pokemon.is_default ? 'Yes' : 'No'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Base Stats -->

            <Card>
                <CardHeader>
                    <CardTitle>Base Stats</CardTitle>
                    <CardDescription>
                        Core Pokémon battle statistics
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="space-y-4">

                        {#each [
                            ['HP', pokemon.hp],
                            ['Attack', pokemon.attack],
                            ['Defense', pokemon.defense],
                            ['Special Attack', pokemon.special_attack],
                            ['Special Defense', pokemon.special_defense],
                            ['Speed', pokemon.speed]
                        ] as [label, value]}

                            <div>
                                <div class="mb-1 flex justify-between">
                                    <span>{label}</span>
                                    <span>{value}</span>
                                </div>

                                <div class="h-3 overflow-hidden rounded bg-muted">
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

            <!-- Pokemon Details -->

            <Card>
                <CardHeader>
                    <CardTitle>Pokémon Details</CardTitle>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-sm text-muted-foreground">
                                Slug
                            </div>
                            <div>{pokemon.slug}</div>
                        </div>

                        <div>
                            <div class="text-sm text-muted-foreground">
                                Pokédex Number
                            </div>
                            <div>{pokemon.pokedex_number}</div>
                        </div>

                        <div>
                            <div class="text-sm text-muted-foreground">
                                Imported
                            </div>
                            <div>
                                {new Date(
                                    pokemon.source_csv_imported_at
                                ).toLocaleString()}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-muted-foreground">
                                TCGdex Synced
                            </div>
                            <div>
                                {new Date(
                                    pokemon.source_tcgdex_synced_at
                                ).toLocaleString()}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-muted-foreground">
                                PokéAPI Synced
                            </div>
                            <div>
                                {new Date(
                                    pokemon.source_pokeapi_synced_at
                                ).toLocaleString()}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Cards -->

            <Card>
                <CardHeader>
                    <CardTitle>
                        Trading Cards ({pokemon.cards.length})
                    </CardTitle>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {#each pokemon.cards as card}
                            <Card>
                                <CardContent class="p-4">
                                    {#if card.image_url !== null}
                                        <img
                                            src={`${card.image_url}/high.webp`}
                                            alt={card.name}
                                            class="mb-4 rounded-lg"
                                        />
                                    {:else}
                                        <p>card image not found for {card.external_id}</p>
                                        <img
                                            src={`${pokemon.sprite_url}`}
                                            alt={card.name}
                                            class="mb-4 rounded-lg"
                                        />
                                    {/if}

                                    <h3 class="font-semibold">
                                        Card Number: {card.external_id}
                                    </h3>
                                    <h3 class="font-semibold">
                                        Card Name: {card.name}
                                    </h3>

                                    <div class="mt-2 space-y-1 text-sm text-muted-foreground">
                                        <div>
                                            HP: {card.hp}
                                        </div>

                                        <div>
                                            Rarity: {card.rarity}
                                        </div>

                                        <div>
                                            Number: {card.number}
                                        </div>
                                    </div>

                                    {#if card.raw_data?.attacks?.length}
                                        <Separator class="my-4" />

                                        <div class="space-y-2">
                                            <h4 class="font-medium">
                                                Attacks
                                            </h4>

                                            {#each card.raw_data.attacks as attack}
                                                <div class="rounded border p-2">
                                                    <div class="font-medium">
                                                        {attack.name}
                                                    </div>

                                                    <div class="text-sm text-muted-foreground">
                                                        Damage:
                                                        {attack.damage}
                                                    </div>

                                                    {#if attack.effect}
                                                        <div class="mt-1 text-xs">
                                                            {attack.effect}
                                                        </div>
                                                    {/if}
                                                </div>
                                            {/each}
                                        </div>
                                    {/if}

                                    {#if card.raw_data?.pricing}
                                        <Separator class="my-4" />

                                        <div class="space-y-2">
                                            <h4 class="font-medium">
                                                Pricing
                                            </h4>

                                            <!-- TCGPlayer -->
                                            {#if card.raw_data.pricing.tcgplayer}
                                                <div class="rounded border p-2 text-sm">
                                                    <div class="font-semibold mb-1">
                                                        TCGPlayer ({card.raw_data.pricing.tcgplayer.unit})
                                                    </div>

                                                    {#if card.raw_data.pricing.tcgplayer.normal}
                                                        <div class="text-muted-foreground">
                                                            Normal:
                                                            low {formatMoney(card.raw_data.pricing.tcgplayer.normal.lowPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                            mid {formatMoney(card.raw_data.pricing.tcgplayer.normal.midPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                            high {formatMoney(card.raw_data.pricing.tcgplayer.normal.highPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                            market {formatMoney(card.raw_data.pricing.tcgplayer.normal.marketPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                        </div>
                                                    {/if}

                                                    {#if card.raw_data.pricing.tcgplayer["reverse-holofoil"]}
                                                        <div class="text-muted-foreground mt-1">
                                                            Reverse holo:
                                                            low {formatMoney(card.raw_data.pricing.tcgplayer["reverse-holofoil"].lowPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                            mid {formatMoney(card.raw_data.pricing.tcgplayer["reverse-holofoil"].midPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                            market {formatMoney(card.raw_data.pricing.tcgplayer["reverse-holofoil"].marketPrice, card.raw_data.pricing.tcgplayer.unit)}
                                                        </div>
                                                    {/if}
                                                </div>
                                            {/if}

                                            <!-- CardMarket -->
                                            {#if card.raw_data.pricing.cardmarket}
                                                <div class="rounded border p-2 text-sm">
                                                    <div class="font-semibold mb-1">
                                                        CardMarket ({card.raw_data.pricing.cardmarket.unit})
                                                    </div>

                                                    <div class="text-muted-foreground">
                                                        avg {formatMoney(card.raw_data.pricing.cardmarket.avg, card.raw_data.pricing.cardmarket.unit)}
                                                        low {formatMoney(card.raw_data.pricing.cardmarket.low, card.raw_data.pricing.cardmarket.unit)}
                                                        trend {formatMoney(card.raw_data.pricing.cardmarket.trend, card.raw_data.pricing.cardmarket.unit)}
                                                    </div>

                                                    {#if card.raw_data.pricing.cardmarket["avg-holo"]}
                                                        <div class="text-muted-foreground mt-1">
                                                            · holo avg {formatMoney(card.raw_data.pricing.cardmarket["avg-holo"],card.raw_data.pricing.cardmarket.unit)}
                                                            · holo low {formatMoney(card.raw_data.pricing.cardmarket["low-holo"],card.raw_data.pricing.cardmarket.unit)}
                                                            · holo trend {formatMoney(card.raw_data.pricing.cardmarket["trend-holo"],card.raw_data.pricing.cardmarket.unit)}
                                                        </div>
                                                    {/if}
                                                </div>
                                            {/if}
                                        </div>
                                    {/if}

                                </CardContent>
                            </Card>
                        {/each}
                    </div>
                </CardContent>
            </Card>

            <!-- Raw Data -->

            <Card>
                <CardHeader>
                    <CardTitle>Developer Data</CardTitle>
                    <CardDescription>
                        Useful while building features
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <button
                        class="font-medium underline"
                        onclick={() => showRawJson = !showRawJson}
                    >
                        {showRawJson ? 'Hide Raw JSON' : 'View Raw JSON'}
                    </button>

                    {#if showRawJson}

                        <div class="space-y-6">

                            <Card>
                                <CardHeader>
                                    <CardTitle>PokeAPI Data</CardTitle>
                                    <CardDescription>
                                        Full stored response from PokéAPI
                                    </CardDescription>
                                </CardHeader>

                                <CardContent>
                                    <JsonTree
                                        label="pokeapi"
                                        value={pokemon.raw_pokeapi}
                                    />
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>TCGdex Data</CardTitle>
                                    <CardDescription>
                                        Full stored response from TCGdex
                                    </CardDescription>
                                </CardHeader>

                                <CardContent>
                                    <JsonTree
                                        label="tcgdex"
                                        value={pokemon.raw_tcgdex}
                                    />
                                </CardContent>
                            </Card>

                        </div>

                    {/if}
                </CardContent>
            </Card>

        {/if}
    </div>