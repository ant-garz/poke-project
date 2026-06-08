<script lang="ts">
    const sections = [
        {
            title: 'Manage Pokémon',
            href: '/admin/pokemon/manage',
            description: 'CRUD create, edit, delete Pokémon entries.',
        },
        {
            title: 'Import Pokémon',
            href: '/admin/pokemon/import',
            description: 'Upload CSV and bulk import Pokémon data.',
        },
    ];
</script>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Pokémon Admin</h1>
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
