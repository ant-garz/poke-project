<script module lang="ts">
    import { dashboard } from '@/routes';

    import TypeFilterButton from '@/components/pokemon/TypeFilterButton.svelte';
    import TypeFilterModal from '@/components/pokemon/TypePickerModal.svelte';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Pokémon Browser',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { onMount } from 'svelte';

    import AppHead from '@/components/AppHead.svelte';

    // UI components
    import Button from '@/components/ui/button/Button.svelte';
    import Input from '@/components/ui/input/Input.svelte';
    import Select from '@/components/ui/select/Select.svelte';
    import Card from '@/components/ui/card/Card.svelte';
    import Skeleton from '@/components/ui/skeleton/Skeleton.svelte';

    import { loadTypes } from '@/stores/types';

    let types: any[] = [];

    let pokemon: any[] = [];
    let loading = false;

    let search = '';
    let primaryType: any = null;
    let secondaryType: any = null;

    let page = 1;
    let meta: any = null;

    const perPage = 20;

    let initialLoaded = false;

    async function fetchPokemon(url = null) {
        loading = true;

        const endpoint = url ?? '/api/v1/public/pokemon';

        const params = new URLSearchParams();

        if (!url) {
            params.append('page', meta?.current_page?.toString() ?? '1');
        }

        if (search) params.append('search', search);
        if (primaryType?.id) {
            params.append('primary_type', primaryType.id.toString());
        }

        if (secondaryType?.id) {
            params.append('secondary_type', secondaryType.id.toString());
        }

        const res = await fetch(`${endpoint}${url ? '' : '?' + params.toString()}`);
        const json = await res.json();

        pokemon = json.data;
        meta = json;

        loading = false;
    }

    // Load types once
    onMount(async () => {
        types = await loadTypes();
        initialLoaded = true;
    });

    function onFilterChange() {
        meta = null;
        fetchPokemon();
    }

    // Reactive reload AFTER initial load
    $: if (initialLoaded) {
        meta = null;
        fetchPokemon();
    }
</script>

<AppHead title="Pokémon Browser" />

<div class="flex flex-col gap-4 p-4">

    <!-- FILTER BAR -->
    <Card class="p-4 space-y-3">

        <!-- ROW 1 -->
        <div class="flex gap-2 items-center">
            <div class="flex-1">
                <Input
                    placeholder="Search Pokémon..."
                    bind:value={search}
                    class="w-full"
                />
            </div>

            <Button onclick={() => fetchPokemon()}>
                Search
            </Button>
        </div>

        <!-- ROW 2 -->
        <div class="flex gap-2 items-center flex-wrap">
            <TypeFilterButton
                label="Primary Type"
                bind:value={primaryType}
                on:change={() => fetchPokemon()}
            />

            <TypeFilterButton
                label="Secondary Type"
                bind:value={secondaryType}
                on:change={() => fetchPokemon()}
            />
        </div>

    </Card>

    <!-- TABLE -->
    <Card class="p-4 overflow-x-auto">

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="p-2">#</th>
                    <th class="p-2">Sprite</th>
                    <th class="p-2">Name</th>
                    <th class="p-2">Types</th>
                </tr>
            </thead>

            <tbody>

                {#if loading}
                    {#each Array(8) as _}
                        <tr class="border-b">
                            <td class="p-2" colspan="4">
                                <Skeleton class="h-6 w-full" />
                            </td>
                        </tr>
                    {/each}
                {:else}

                    {#each pokemon as p}
                        <tr class="border-b hover:bg-muted/50 transition" onclick={()=> {
                            window.location.href="/pokemon/" + p.id
                        }}>

                            <td class="p-2">
                                {p.pokedex_number}
                            </td>

                            <td class="p-2">
                                {#if p.sprite_url}
                                    <img
                                        src={p.sprite_url}
                                        alt={p.name}
                                        class="w-10 h-10 object-contain"
                                        loading="lazy"
                                    />
                                {:else}
                                    <div class="w-10 h-10 bg-muted rounded" />
                                {/if}
                            </td>

                            <td class="p-2 font-medium">
                                {p.name}
                            </td>

                            <td class="p-2 text-muted-foreground">
                                {p.primary_type?.name ?? '-'}

                                {#if p.secondary_type}
                                    {' / ' + p.secondary_type.name}
                                {/if}
                            </td>

                        </tr>
                    {/each}

                {/if}

            </tbody>
        </table>

    </Card>

    <!-- PAGINATION -->
    <div class="flex items-center gap-2 w-75 mx-auto">

        <Button
            onclick={() => {
                if (meta?.prev_page_url) {
                    fetchPokemon(meta.prev_page_url);
                }
            }}
            disabled={!meta?.prev_page_url}
        >
            Prev
        </Button>

        <span class="text-sm">Page {meta?.current_page} / {meta?.last_page}</span>
        <Button
            onclick={() => {
                if (meta?.next_page_url) {
                    fetchPokemon(meta.next_page_url);
                }
            }}
            disabled={!meta?.next_page_url}
        >
            Next
        </Button>

    </div>

</div>