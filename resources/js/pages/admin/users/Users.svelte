<script lang="ts">
    import { onMount } from 'svelte';

    let users: any[] = [];
    let loading = true;

    async function load() {
        loading = true;

        const response = await fetch(
            '/api/v1/admin/users',
            {
                credentials: 'include',
            }
        );

        const data = await response.json();

        users = data.data;

        loading = false;
    }

    onMount(load);
</script>

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-semibold">
            Users
        </h1>

        <p class="text-sm text-gray-500">
            Manage application users
        </p>
    </div>

    {#if loading}
        <div class="text-sm text-gray-500">
            Loading users...
        </div>
    {:else}

        <div class="rounded border divide-y">

            {#each users as user}

                <a
                    href={`/admin/users/${user.id}`}
                    class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-900"
                >

                    <div class="flex items-center justify-between">

                        <div>
                            <div class="font-medium">
                                {user.name}
                            </div>

                            <div class="text-sm text-gray-500">
                                {user.email}
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            ID #{user.id}
                        </div>

                    </div>

                </a>

            {/each}

        </div>

    {/if}

</div>