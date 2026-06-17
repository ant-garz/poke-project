<script lang="ts">
    import JsonTree from './JsonTree.svelte';

    interface Props {
        label?: string;
        value: unknown;
        depth?: number;
    }

    let {
        label = 'root',
        value,
        depth = 0,
    }: Props = $props();

    let open = $state(depth < 1);

    const isObject = (v: unknown): v is Record<string, unknown> =>
        typeof v === 'object' &&
        v !== null &&
        !Array.isArray(v);

    const isArray = (v: unknown): v is unknown[] =>
        Array.isArray(v);

    const isPrimitive = (v: unknown) =>
        !isObject(v) && !isArray(v);

    const valueClass = (v: unknown) => {
        if (v === null) {
            return 'text-muted-foreground';
        }

        switch (typeof v) {
            case 'string':
                return 'text-green-600 dark:text-green-400';

            case 'number':
                return 'text-blue-600 dark:text-blue-400';

            case 'boolean':
                return 'text-purple-600 dark:text-purple-400';

            default:
                return '';
        }
    };
</script>

{#if isPrimitive(value)}

    <div class="flex gap-2 py-0.5 text-sm">
        <span class="font-medium">{label}:</span>

        <span class={valueClass(value)}>
            {#if typeof value === 'string'}
                "{value}"
            {:else if value === null}
                null
            {:else}
                {String(value)}
            {/if}
        </span>
    </div>

{:else}

    <div class="py-1">
        <button
            class="flex items-center gap-2 text-sm font-medium hover:text-primary"
            onclick={() => open = !open}
        >
            <span>
                {open ? '▼' : '▶'}
            </span>

            <span>{label}</span>

            {#if isArray(value)}
                <span class="text-muted-foreground">
                    [{value.length}]
                </span>
            {:else}
                <span class="text-muted-foreground">
                    {Object.keys(value).length} fields
                </span>
            {/if}
        </button>

        {#if open}
            <div class="ml-6 mt-1 border-l pl-4">

                {#if isArray(value)}

                    {#each value as item, index}
                        <JsonTree
                            label={String(index)}
                            value={item}
                            depth={depth + 1}
                        />
                    {/each}

                {:else if isObject(value)}

                    {#each Object.entries(value) as [key, val]}
                        <JsonTree
                            label={key}
                            value={val}
                            depth={depth + 1}
                        />
                    {/each}

                {/if}

            </div>
        {/if}
    </div>

{/if}