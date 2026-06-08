<script lang="ts">
    console.log('admin manage');
</script>


    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Manage Pokémon</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Placeholder CRUD area. Schema not connected yet.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <button class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                + Add Pokémon
            </button>

            <button class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                Refresh
            </button>
        </div>

        <!-- Dummy table -->
        <div class="rounded-lg border">
            <div class="border-b p-3 text-sm font-medium">
                Pokémon List (Mock Data)
            </div>

            <div class="p-4 space-y-2">
                {#each Array(5) as _, i}
                    <div class="flex justify-between rounded border p-2 text-sm">
                        <span>Pokémon #{i + 1}</span>

                        <div class="flex gap-2">
                            <button class="text-blue-500 hover:underline">Edit</button>
                            <button class="text-red-500 hover:underline">Delete</button>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </div>
