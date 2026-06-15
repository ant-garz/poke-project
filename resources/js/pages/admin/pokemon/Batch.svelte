<script lang="ts">
    export let id: number;

    let batch: any = null;

    async function load() {
        const res = await fetch(`/api/v1/admin/pokemon/import/batches/${id}`, {
            credentials: 'include'
        });

        batch = await res.json();
        console.log(batch)
    }

    load();
</script>

{#if batch}
    <div class="space-y-4">

        <h1 class="text-xl font-semibold">
            Batch #{batch.id}
        </h1>

        <div class="rounded border p-4 space-y-2">

            <div>Status: {batch.status}</div>

            <div>
                Progress: {batch.progress}%
            </div>

            <div>
                Rows: {batch.processed_rows} / {batch.total_rows}
            </div>

            <div>
                Failed: {batch.failed_rows}
            </div>

        </div>

    </div>
{:else}
    <div>Loading...</div>
{/if}