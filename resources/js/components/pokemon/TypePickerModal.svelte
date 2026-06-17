<script lang="ts">
    import { onMount } from 'svelte';
    import { loadTypes } from '@/stores/types';

    export let open = false;

    // Svelte 5 style: callback props (no event dispatcher)
    export let onSelect = (type: any) => {};
    export let onClose = () => {};

    let types: any[] = [];

    onMount(async () => {
        types = await loadTypes();
    });

    function select(type: any) {
        onSelect(type);
    }

    function close() {
        onClose();
    }
</script>

{#if open}
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-background p-4 rounded-lg w-96 shadow-lg">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Select Type</h2>

                <button class="text-sm" on:click={close}>
                    ✕
                </button>
            </div>

            <!-- TYPE GRID -->
            <div class="grid grid-cols-2 gap-2 max-h-80 overflow-auto">
                {#each types as t}
                    <button
                        style={`background-color:${t.color};color:${t.text_color}`}

                        class="p-2 border rounded hover:bg-muted text-left font-medium"
                        on:click={() => select(t)}
                    >
                        {t.name}
                    </button>
                {/each}
            </div>

            <!-- CLEAR -->
            <button
                class="mt-3 text-sm underline"
                on:click={() => select(null)}
            >
                Clear selection
            </button>

        </div>
    </div>
{/if}