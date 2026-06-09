<script lang="ts">
    import { onMount } from 'svelte';

    let batches: any[] = [];
    let loading = true;

    async function load() {
        loading = true;

        const res = await fetch('/api/v1/admin/pokemon/import/batches', {
            credentials: 'include'
        });

        const data = await res.json();

        batches = data.data;
        loading = false;
    }

    onMount(load);
</script>

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">Import Batches</h1>
        <p class="text-sm text-gray-500">
            History of all Pokémon CSV imports
        </p>
    </div>

    {#if loading}
        <div class="text-sm text-gray-500">Loading...</div>
    {:else}

        <div class="space-y-2">

            {#each batches as batch}
                <a
                    href={`/admin/pokemon/batches/${batch.id}`}
                    class="block rounded border p-3 hover:bg-gray-50 dark:hover:bg-gray-900"
                >

                    <div class="flex justify-between">

                        <div>
                            <div class="font-medium">
                                {batch.original_filename}
                            </div>

                            <div class="text-xs text-gray-500">
                                ID #{batch.id}
                            </div>
                        </div>

                        <div class="text-right text-sm">
                            <div>{batch.status}</div>
                            <div class="text-xs text-gray-500">
                                {batch.processed_rows}/{batch.total_rows}
                            </div>
                        </div>

                    </div>

                </a>
            {/each}

        </div>

    {/if}

</div>