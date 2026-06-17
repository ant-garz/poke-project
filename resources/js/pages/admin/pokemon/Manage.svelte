<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin / Manage Pokémon',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { onMount } from 'svelte';

    import AppHead from '@/components/AppHead.svelte';

    import Button from '@/components/ui/button/Button.svelte';
    import Input from '@/components/ui/input/Input.svelte';
    import Select from '@/components/ui/select/Select.svelte';
    import Card from '@/components/ui/card/Card.svelte';
    import Skeleton from '@/components/ui/skeleton/Skeleton.svelte';

    let pokemon: any[] = [];
    let loading = false;

    let search = '';
    let primaryType = '';
    let secondaryType = '';

    let meta: any = null;

    let initialLoaded = false;

    async function fetchPokemon(url = null) {
        loading = true;

        const endpoint = '/api/v1/public/pokemon';

        const params = new URLSearchParams();

        if (!url) {
            params.append('page', meta?.current_page?.toString() ?? '1');
        }

        if (search) params.append('search', search);
        if (primaryType) params.append('primary_type', primaryType);
        if (secondaryType) params.append('secondary_type', secondaryType);

        const res = await fetch(`${endpoint}${url ? '' : '?' + params.toString()}`);
        const json = await res.json();

        pokemon = json.data;
        meta = json;

        loading = false;
    }

    onMount(async () => {
        initialLoaded = true;
    });

    $: if (initialLoaded) {
        meta = null;
        fetchPokemon();
    }
</script>

<AppHead title="Admin Pokémon Manager" />

<div class="flex flex-col gap-4 p-4">

    <!-- FILTER BAR -->
    <Card class="p-4 flex gap-2 items-center flex-wrap">

        <Input
            placeholder="Search Pokémon..."
            bind:value={search}
            class="w-64"
        />

        <Button onclick={() => fetchPokemon()}>
            Search
        </Button>

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
                    <th class="p-2 text-right"></th>
                </tr>
            </thead>

            <tbody>

                {#if loading}
                    {#each Array(8) as _}
                        <tr class="border-b">
                            <td class="p-2" colspan="5">
                                <Skeleton class="h-6 w-full" />
                            </td>
                        </tr>
                    {/each}
                {:else}

                    {#each pokemon as p}
                        <tr class="border-b hover:bg-muted/50 transition">

                            <td class="p-2">
                                {p.pokedex_number}
                            </td>

                            <td class="p-2">
                                {#if p.artwork_url}
                                    <img
                                        src={p.artwork_url}
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

                            <!-- ADMIN ACTIONS -->
                            <td class="p-2 text-right space-x-2">

                                <Button
                                    onclick={() => {
                                        window.location.href = `/admin/pokemon/${p.id}`;
                                    }}
                                >
                                    Open
                                </Button>

                            </td>

                        </tr>
                    {/each}

                {/if}

            </tbody>
        </table>

    </Card>

    <!-- PAGINATION -->
    <div class="flex items-center gap-2">

        <Button
            onclick={() => meta?.prev_page_url && fetchPokemon(meta.prev_page_url)}
            disabled={!meta?.prev_page_url}
        >
            Prev
        </Button>

        <span class="text-sm">
            Page {meta?.current_page} / {meta?.last_page}
        </span>

        <Button
            onclick={() => meta?.next_page_url && fetchPokemon(meta.next_page_url)}
            disabled={!meta?.next_page_url}
        >
            Next
        </Button>

    </div>

</div>