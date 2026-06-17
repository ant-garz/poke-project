<script lang="ts">
    import TypePickerModal from './TypePickerModal.svelte';

    export let label = 'Type';
    export let value: any = null;

    let open = false;

    function setType(type: any) {
        value = type;
        open = false;

        // notify parent
        dispatchChange();
    }

    function clear() {
        value = null;
        dispatchChange();
    }

    function dispatchChange() {
        dispatch('change', value);
    }

    import { createEventDispatcher } from 'svelte';
    const dispatch = createEventDispatcher();
</script>

<div class="flex items-center gap-2">

    <!-- TRIGGER -->
    <button
        class="px-3 py-1 border rounded hover:bg-muted transition"
        style={`background-color:${value?.color};color:${value?.text_color}`}
        on:click={() => open = true}
    >
        {label}:
        <span class="font-medium">
            {value?.name ?? 'Any'}
        </span>
    </button>

    <!-- CLEAR -->
    {#if value}
        <button
            class="text-xs text-red-500"
            on:click={clear}
        >
            clear
        </button>
    {/if}

</div>

<!-- MODAL -->
<TypePickerModal
    bind:open
    onSelect={setType}
    onClose={() => open = false}
/>