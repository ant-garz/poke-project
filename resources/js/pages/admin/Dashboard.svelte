<script lang="ts">
    import { useAuth } from '@/lib/useAuth';

    const { isAdmin } = useAuth();
    const showAdmin = isAdmin();

    const sections = [
            {
            title: 'Manage Users',
            href: '/admin/users',
            description: 'Update user profile infomrmation',
        },
        {
            title: 'Manage Pokémon',
            href: '/admin/pokemon/manage',
            description: 'Create, update, or delete individual Pokémon entries.',
        },
        {
            title: 'Import Pokémon',
            href: '/admin/pokemon/import',
            description: 'Upload CSV and bulk import Pokémon data.',
        },
        {
            title: 'Batch Import History',
            href: '/admin/pokemon/batches',
            description: 'View results of previous batch imports of Pokémon data.',
        },
    ];
</script>

<h1 class="text-2xl font-semibold">Admin Dashboard</h1>

{#if showAdmin}

    <div class="space-y-6">
    <p class="text-sm text-gray-500">Choose an action below.</p>

    <div class="grid gap-4 md:grid-cols-2">
        {#each sections as section}
            <a
                href={section.href}
                class="block rounded-lg border p-4 transition
                    hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <h2 class="font-medium text-gray-900 dark:text-gray-100">
                    {section.title}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {section.description}
                </p>
            </a>
        {/each}
    </div>
</div>
{:else}
    <p class="text-red-500 mt-4">
        You are not authorized to access admin features.
    </p>
{/if}