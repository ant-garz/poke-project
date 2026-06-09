<script lang="ts">
    let file: File | null = null;

    let interval: any = null;
    let progress: number = 0;
    let status: string = '';

    let uploading = false;
    let error: string | null = null;
    let success: string | null = null;

    let batchId: number | null = null;

    let preview: Record<string, string>[] = [];
    let parsed = false;

    function handleFileSelect(event: Event) {
        const target = event.target as HTMLInputElement;
        const selected = target.files?.[0];

        if (!selected) return;

        error = null;
        success = null;
        preview = [];
        parsed = false;

        if (!selected.name.toLowerCase().endsWith('.csv')) {
            error = 'Only CSV files are allowed';
            return;
        }

        if (selected.size > 5 * 1024 * 1024) {
            error = 'File too large (max 5MB)';
            return;
        }

        file = selected;

        // 👇 parse file for preview
        const reader = new FileReader();

        reader.onload = (e) => {
            const text = e.target?.result as string;

            preview = parseCSV(text);
            parsed = true;
        };

        reader.readAsText(selected);
    }

    async function upload() {
        if (!file || !parsed || preview.length === 0) {
            error = 'Please select and preview a valid CSV first';
            return;
        }

        uploading = true;
        error = null;
        success = null;
        batchId = null;

        try {
            const formData = new FormData();
            formData.append('file', file);

            const res = await fetch('/api/v1/admin/pokemon/import', {
                method: 'POST',
                body: formData,
                credentials: 'include',
            });

            if (!res.ok) {
                const message = await res.text();
                throw new Error(message || 'Upload failed');
            }

            const data = await res.json();

            batchId = data.batch_id;
            success = `Import started successfully (Batch #${batchId})`;

            startPolling(batchId);

            // reset file after successful upload
            file = null;

            // reset input visually
            const input = document.getElementById(
                'csvInput',
            ) as HTMLInputElement;
            if (input) input.value = '';
        } catch (e: any) {
            error = e?.message ?? 'Something went wrong during upload';
        } finally {
            uploading = false;
        }
    }

    async function startPolling(id: number) {
        if (interval) clearInterval(interval);

        interval = setInterval(async () => {
            const res = await fetch(`/api/v1/admin/pokemon/import/${id}`, {
                credentials: 'include'
            });

            const data = await res.json();

            progress = data.progress;
            status = data.status;

            if (status === 'completed' || status === 'failed') {
                clearInterval(interval);
            }
        }, 1500);
    }

    function clear() {
        file = null;
        error = null;
        success = null;
        batchId = null;

        const input = document.getElementById('csvInput') as HTMLInputElement;
        if (input) input.value = '';
    }

    function parseCSV(text: string) {
        const lines = text
            .trim()
            .split('\n')
            .filter(Boolean);

        const headers = lines[0]
            .split(',')
            .map(h => h.trim());

        return lines.slice(1).map(line => {
            const values = line
                .split(',')
                .map(v => v.trim());

            const row: Record<string, string> = {};

            headers.forEach((header, i) => {
                row[header] = values[i] ?? '';
            });

            return row;
        });
    }
</script>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-semibold">Import Pokémon</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Upload a CSV file to start the ingestion pipeline.
        </p>
    </div>

    <!-- Drop / Upload Area -->
    <div class="rounded-lg border border-dashed p-10 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Drag & drop CSV file here (or click select)
        </p>

        <input
            id="csvInput"
            type="file"
            accept=".csv"
            class="hidden"
            on:change={handleFileSelect}
        />

        <button
            class="mt-4 rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
            on:click={() => document.getElementById('csvInput')?.click()}
        >
            Select File
        </button>

        {#if file}
            <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Selected: <strong>{file.name}</strong>
            </div>
        {/if}
    </div>

    <!-- Status Messages -->
    {#if error}
        <div
            class="rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-500"
        >
            {error}
        </div>
    {/if}

    {#if success}
        <div
            class="rounded-md border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-500"
        >
            {success}
        </div>
    {/if}

    {#if batchId}
        <div class="rounded-md border p-3 text-sm space-y-2">

            <div>
                Batch ID: <strong>{batchId}</strong>
            </div>

            <div>
                Status: <strong>{status}</strong>
            </div>

            <div class="w-full bg-gray-200 rounded h-2">
                <div
                    class="bg-green-500 h-2 rounded"
                    style="width: {progress}%"
                ></div>
            </div>

            <div class="text-xs text-gray-500">
                {progress}% complete
            </div>

        </div>

         <div class="rounded-md border border-blue-500/20 bg-blue-500/10 p-3 text-sm text-blue-600 dark:text-blue-400 space-y-1">

        <div class="font-medium">
            Import in progress
        </div>

        <div class="text-xs leading-relaxed text-gray-600 dark:text-gray-300">
            You can safely leave this page. The import will continue running in the background.
        </div>

        <div class="text-xs text-gray-500">
            You can check progress anytime in the
            <a
                href="/admin/pokemon/batches"
                class="underline hover:text-blue-500"
            >
                batch history page
            </a>.
        </div>

        </div>
    {/if}

    {#if preview.length > 0 && success === null && error === null}
        <div class="rounded-lg border">
            <div class="border-b p-3 text-sm font-medium">
                Import Preview ({preview.length} rows)
            </div>

            <div class="p-4 space-y-2 text-sm max-h-64 overflow-auto">
                {#each preview as row, i}
                    <div class="rounded border p-2 flex justify-between">
                        <span>
                            Row {i + 1} → {row.Name}
                        </span>

                        <span class="text-gray-500">
                            #{row.Number}
                        </span>
                    </div>
                {/each}
            </div>
        </div>
    {/if}

    <!-- Actions -->
    <div class="flex gap-2">
        <button
            class="rounded-md border px-4 py-2 text-sm hover:bg-green-100 dark:hover:bg-green-900/30 disabled:opacity-50"
            on:click={upload}
            disabled={uploading || !parsed || preview.length === 0 || success !== null || error !== null}
        >
            {#if uploading}
                Uploading...
            {:else}
                Start Import
            {/if}
        </button>

        <button
            class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
            on:click={clear}
            disabled={success !== null || error !== null}
        >
            Clear
        </button>
    </div>
</div>
