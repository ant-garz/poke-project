<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
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

    let audioEl;
    let pollInterval: ReturnType<typeof setInterval> | null = null;

    // -----------------------------
    // UI HELPERS
    // -----------------------------
    function playAudio() {
        audioEl.play().catch(console.error);
    }

    function getStatusStyle(status: string) {
        switch (status) {
            case 'idle':
                return 'bg-gray-200 text-gray-800';
            case 'queued':
                return 'bg-blue-100 text-blue-800';
            case 'processing':
                return 'bg-yellow-100 text-yellow-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'failed':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function isActiveStatus(status: string) {
        return status === 'queued' || status === 'processing';
    }

    // -----------------------------
    // POLLING CONTROL
    // -----------------------------
    function startPolling() {
        if (pollInterval) return;

        pollInterval = setInterval(fetchPokemon, 3000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    // -----------------------------
    // DATA FETCH
    // -----------------------------
    async function fetchPokemon() {
        const res = await fetch(`/api/v1/public/pokemon/${pokemonId}`);
        const data = await res.json();

        pokemon = data;
        loading = false;

        // polling decision is ALWAYS based on fresh API response
        if (isActiveStatus(data.tcgdex_sync_status)) {
            startPolling();
        } else {
            stopPolling();
        }
    }

    // -----------------------------
    // UPDATE
    // -----------------------------
    async function updatePokemon() {
        saving = true;

        await fetch(`/api/v1/admin/pokemon/${pokemonId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(pokemon)
        });

        saving = false;
        await fetchPokemon();
    }

    // -----------------------------
    // SYNC (IMPORTANT FIX)
    // -----------------------------
    async function sync(source: 'pokeapi' | 'tcgdex') {
        saving = true;

        await fetch(`/api/v1/admin/pokemon/${pokemonId}/sync/${source}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        saving = false;

        // 🔥 CRITICAL: force polling immediately after triggering sync
        await fetchPokemon();
        startPolling();
    }

    // -----------------------------
    // DELETE / RESTORE
    // -----------------------------
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

    onDestroy(() => {
        stopPolling();
    });
</script>

<svelte:head>
    <title>{pokemon ? `Admin · ${pokemon.name}` : 'Loading'}</title>
</svelte:head>

<div class="container mx-auto space-y-6 p-6">

    <h1>Admin</h1>

    {#if loading}
        <Skeleton class="h-64 w-full" />
    {:else if pokemon}

        <!-- HEADER -->
        <Card>
            <CardContent class="p-6">
                <div class="flex gap-6">

                    <img
                        src={pokemon.pokeapi_artwork_url}
                        class="h-40 w-40 object-contain"
                        alt={pokemon.name}
                    />

                    <div class="flex-1 space-y-2">

                        <h1 class="text-3xl font-bold">
                            #{pokemon.pokedex_number} {pokemon.name}
                        </h1>

                        <!-- TYPES -->
                        <div class="flex gap-2 flex-wrap">
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

                        <!-- STATUS VISUAL) -->
                        <div class="mt-2 text-sm">
                            <span class="text-gray-500">TCGdex status:</span>
                            <span class={`ml-2 px-2 py-1 rounded text-xs font-semibold ${getStatusStyle(pokemon.tcgdex_sync_status)}`}>
                                {pokemon.tcgdex_sync_status}
                            </span>
                        </div>

                        <Button onclick={playAudio} class="mt-4">
                            Play Audio
                        </Button>

                        <audio bind:this={audioEl} src={pokemon.cry_url}></audio>

                        <Separator class="my-4" />

                        <!-- ACTIONS -->
                        <div class="flex gap-2 flex-wrap">

                            <Button disabled={saving} onclick={() => sync('pokeapi')}>
                                Sync PokéAPI
                            </Button>

                            <Button disabled={saving} onclick={() => sync('tcgdex')}>
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

        <!-- FORM -->
        <Card>
            <CardHeader>
                <CardTitle>Core Pokémon Data</CardTitle>
                <CardDescription>Editable canonical database fields</CardDescription>

                <Button onclick={updatePokemon} disabled={saving} class="mt-2">
                    Save Changes
                </Button>
            </CardHeader>

            <CardContent class="space-y-4">

                <div class="grid gap-4">

                    <div>
                        <label>Name</label>
                        <Input bind:value={pokemon.name} />
                    </div>

                    <div>
                        <label>Slug</label>
                        <Input bind:value={pokemon.slug} />
                    </div>

                    <div>
                        <label>HP</label>
                        <Input type="number" bind:value={pokemon.hp} />
                    </div>

                    <div>
                        <label>Attack</label>
                        <Input type="number" bind:value={pokemon.attack} />
                    </div>

                    <div>
                        <label>Defense</label>
                        <Input type="number" bind:value={pokemon.defense} />
                    </div>

                    <div>
                        <label>Special Attack</label>
                        <Input type="number" bind:value={pokemon.special_attack} />
                    </div>

                    <div>
                        <label>Special Defense</label>
                        <Input type="number" bind:value={pokemon.special_defense} />
                    </div>

                    <div>
                        <label>Speed</label>
                        <Input type="number" bind:value={pokemon.speed} />
                    </div>

                    <div>
                        <label>Height</label>
                        <Input type="number" bind:value={pokemon.height} />
                    </div>

                    <div>
                        <label>Weight</label>
                        <Input type="number" bind:value={pokemon.weight} />
                    </div>

                    <div>
                        <label>Base Experience</label>
                        <Input type="number" bind:value={pokemon.base_experience} />
                    </div>

                    <div>
                        <label>Sprite URL</label>
                        <Input bind:value={pokemon.sprite_url} />
                    </div>

                    <div>
                        <label>PokéAPI Artwork</label>
                        <Input bind:value={pokemon.pokeapi_artwork_url} />
                    </div>

                    <div>
                        <label>TCGdex Artwork</label>
                        <Input bind:value={pokemon.tcgdex_artwork_base_url} />
                    </div>

                    <div>
                        <label>Cry URL</label>
                        <Input bind:value={pokemon.cry_url} />
                    </div>

                </div>

                <textarea
                    bind:value={pokemon.description}
                    class="w-full mt-4"
                    placeholder="Description"
                />

            </CardContent>
        </Card>

        <!-- RAW -->
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