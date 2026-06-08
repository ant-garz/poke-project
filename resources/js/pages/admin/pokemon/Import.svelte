<script lang="ts">
    console.log('admin pokemon import');
</script>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Import Pokémon</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Placeholder CSV import area. Upload + parsing not implemented yet.
            </p>
        </div>

        <!-- Drop zone mock -->
        <div class="rounded-lg border border-dashed p-10 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Drag & drop CSV file here (UI only)
            </p>

            <button class="mt-4 rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                Select File
            </button>
        </div>

        <!-- Mock preview -->
        <div class="rounded-lg border">
            <div class="border-b p-3 text-sm font-medium">
                Import Preview (Mock)
            </div>

            <div class="p-4 space-y-2 text-sm">
                <div>Row 1 → Pikachu</div>
                <div>Row 2 → Charizard</div>
                <div>Row 3 → Bulbasaur</div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <button class="rounded-md border px-4 py-2 text-sm hover:bg-green-100 dark:hover:bg-green-900/30">
                Simulate Import
            </button>

            <button class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                Clear
            </button>
        </div>
    </div>