<script lang="ts">
    import { onMount } from 'svelte';

    export let userId: number;

    let user: any = null;
    let loading = true;
    let saving = false;

    async function load() {
        loading = true;
        console.log('userId', userId);
        const response = await fetch(
            `/api/v1/admin/users/${userId}`,
            {
                credentials: 'include',
            }
        );

        user = await response.json();

        loading = false;
    }

    async function softDelete() {
        saving = true;

        await fetch(`/api/v1/admin/users/${user.id}`, {
            method: 'DELETE',
            credentials: 'include',
        });

        await load();

        saving = false;
    }

    async function restoreUser() {
        saving = true;

        await fetch(`/api/v1/admin/users/${user.id}/restore`, {
            method: 'PATCH',
            credentials: 'include',
        });

        await load();

        saving = false;
    }

    onMount(load);
</script>

<div class="space-y-6">

    {#if loading}

        <div class="text-sm text-gray-500">
            Loading user...
        </div>

    {:else}

        <div>

            <h1 class="text-2xl font-semibold">
                {user.name}
            </h1>

            <p class="text-sm text-gray-500">
                User Details
            </p>

        </div>

        <div class="rounded border p-6 space-y-6">

            <div>

                <div class="text-xs uppercase text-gray-500">
                    Name
                </div>

                <div class="font-medium">
                    {user.name}
                </div>

            </div>

            <div>

                <div class="text-xs uppercase text-gray-500">
                    Email
                </div>

                <div class="font-medium">
                    {user.email}
                </div>

            </div>

            <div class="border-t pt-4 space-y-2">

            <div class="text-xs uppercase text-gray-500">
                Roles
            </div>

            {#if user.roles.length > 0}

                <div class="flex flex-wrap gap-2">

                    {#each user.roles as role}
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-800">
                            {role}
                        </span>
                    {/each}

                </div>

            {:else}

                <div class="text-sm text-gray-500">
                    No roles assigned
                </div>

            {/if}

        </div>

        <div class="border-t pt-4 space-y-3">

            <div class="text-xs uppercase text-gray-500">
                Account Status
            </div>

            {#if user.deleted_at}

                <div class="text-sm text-red-500">
                    Deleted at: {user.deleted_at}
                </div>

                <button
                    class="px-3 py-1 text-sm rounded bg-green-600 text-white"
                    on:click={restoreUser}
                    disabled={saving}
                >
                    Restore User
                </button>

            {:else}

                <div class="text-sm text-green-600">
                    Active
                </div>

                <button
                    class="px-3 py-1 text-sm rounded bg-red-600 text-white"
                    on:click={softDelete}
                    disabled={saving}
                >
                    Soft Delete User
                </button>

            {/if}

        </div>

        </div>

        <div>

            <a
                href="/admin/users"
                class="text-sm hover:underline"
            >
                ← Back to Users
            </a>

        </div>

    {/if}

</div>