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
                        <Badge>{pokemon.primary_type.name}</Badge>
                    {/if}
                    {#if pokemon.secondary_type}
                        <Badge>{pokemon.secondary_type.name}</Badge>
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
                                <Separator class="my-3" />

                                <div class="text-sm">
                                    <div class="font-medium">{card.set.name}</div>
                                    {#if card.set.logo_url}
                                        <img
                                            src={card.set.logo_url + '.webp'}
                                            class="h-8 mt-2"
                                            loading="lazy"
                                        />
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