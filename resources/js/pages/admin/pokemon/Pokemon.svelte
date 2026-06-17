<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '@inertiajs/svelte';

    import Card from '@/components/ui/card/Card.svelte';
    import CardHeader from '@/components/ui/card/CardHeader.svelte';
    import CardTitle from '@/components/ui/card/CardTitle.svelte';
    import CardContent from '@/components/ui/card/CardContent.svelte';
    import CardDescription from '@/components/ui/card/CardDescription.svelte';

    import Button from '@/components/ui/button/Button.svelte';
    import Input from '@/components/ui/input/Input.svelte';
    import Badge from '@/components/ui/badge/Badge.svelte';
    import Separator from '@/components/ui/separator/Separator.svelte';
    import Skeleton from '@/components/ui/skeleton/Skeleton.svelte';

    import JsonTree from '@/components/JsonTree.svelte';

    const pokemonId = page.url.split('/').pop();

    let loading = $state(true);
    let saving = $state(false);
    let showRaw = $state(false);

    let pokemon = $state<any>(null);

    async function fetchPokemon() {
        loading = true;

        const res = await fetch(`/api/v1/public/pokemon/${pokemonId}`,{
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        pokemon = await res.json();

        loading = false;
    }

    async function updatePokemon() {
        saving = true;

        await fetch(`/api/v1/admin/pokemon/${pokemonId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pokemon)
        });

        saving = false;
        await fetchPokemon();
    }

    async function sync(source: 'pokeapi' | 'tcgdex') {
        await fetch(`/api/v1/admin/pokemon/${pokemonId}/sync/${source}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ source })
        });

        await fetchPokemon();
    }

    async function destroyPokemon() {
        await fetch(`/api/v1/admin/pokemon/${pokemonId}`, {
            method: 'DELETE'
        });

        window.location.href = '/admin/pokemon';
    }

    async function restorePokemon() {
        await fetch(`/api/v1/admin/pokemon/${pokemonId}/restore`, {
            method: 'POST'
        });

        await fetchPokemon();
    }

    onMount(fetchPokemon);
</script>

<svelte:head>
    <title>{pokemon ? `Admin · ${pokemon.name}` : 'Loading'}</title>
</svelte:head>

<div class="container mx-auto space-y-6 p-6">

    {#if loading}
        <Skeleton class="h-64 w-full" />
    {:else if pokemon}

        <!-- HEADER -->
        <Card>
            <CardContent class="p-6">
                <div class="flex gap-6">
                    <img
                        src={pokemon.artwork_url}
                        class="h-40 w-40 object-contain"
                    />

                    <div class="flex-1 space-y-2">
                        <h1 class="text-3xl font-bold">
                            #{pokemon.pokedex_number} {pokemon.name}
                        </h1>

                        <div class="flex gap-2 flex-wrap">
                            {#if pokemon.primary_type}
                                <Badge>{pokemon.primary_type.name}</Badge>
                            {/if}
                            {#if pokemon.secondary_type}
                                <Badge>{pokemon.secondary_type.name}</Badge>
                            {/if}
                        </div>

                        <div class="flex gap-2 mt-4 flex-wrap">

                            <Button onclick={updatePokemon} disabled={saving}>
                                Save Changes
                            </Button>

                            <Button onclick={() => sync('pokeapi')}>
                                Sync PokéAPI
                            </Button>

                            <Button onclick={() => sync('tcgdex')}>
                                Sync TCGdex
                            </Button>

                            {#if pokemon.deleted_at}
                                <Button onclick={restorePokemon}>
                                    Restore
                                </Button>
                            {/if}

                            <Button variant="destructive" onclick={destroyPokemon}>
                                Delete
                            </Button>

                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- EDIT FORM -->
        <Card>
            <CardHeader>
                <CardTitle>Core Pokémon Data</CardTitle>
                <CardDescription>
                    Editable canonical database fields
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">

                <div class="grid md:grid-cols-2 gap-4">

                    <Input bind:value={pokemon.name} placeholder="Name" />
                    <Input bind:value={pokemon.slug} placeholder="Slug" />

                    <Input type="number" bind:value={pokemon.hp} placeholder="HP" />
                    <Input type="number" bind:value={pokemon.attack} placeholder="Attack" />
                    <Input type="number" bind:value={pokemon.defense} placeholder="Defense" />
                    <Input type="number" bind:value={pokemon.special_attack} placeholder="Sp. Attack" />
                    <Input type="number" bind:value={pokemon.special_defense} placeholder="Sp. Defense" />
                    <Input type="number" bind:value={pokemon.speed} placeholder="Speed" />

                    <Input type="number" bind:value={pokemon.height} placeholder="Height" />
                    <Input type="number" bind:value={pokemon.weight} placeholder="Weight" />
                    <Input type="number" bind:value={pokemon.base_experience} placeholder="Base XP" />

                    <Input bind:value={pokemon.sprite_url} placeholder="Sprite URL" />
                    <Input bind:value={pokemon.artwork_url} placeholder="Artwork URL" />
                    <Input bind:value={pokemon.cry_url} placeholder="Cry URL" />

                </div>

                <textarea
                    bind:value={pokemon.description}
                    placeholder="Description"
                    class="w-full"
                ></textarea>

            </CardContent>
        </Card>

        <!-- RAW DATA (OPTIONAL DEBUG) -->
        <Card>
            <CardHeader>
                <CardTitle>Raw Data</CardTitle>
            </CardHeader>

            <CardContent>
                <Button onclick={() => showRaw = !showRaw}>
                    Toggle Raw JSON
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