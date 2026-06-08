<script lang="ts">
    import { useAuth } from '@/lib/useAuth';

    const { isAdmin } = useAuth();
    const showAdmin = isAdmin();
</script>

<h1 class="text-2xl font-semibold">Admin Dashboard</h1>

{#if showAdmin}
    <div class="mt-6 space-y-4">
        <a href="/admin/users" class="block rounded border p-4 hover:bg-gray-50 dark:hover:bg-gray-800 w-75 mx-auto">
            Manage Users
        </a>

        <a href="/admin/roles" class="block rounded border p-4 hover:bg-gray-50 dark:hover:bg-gray-800 w-75 mx-auto">
            Manage Roles
        </a>
    </div>
{:else}
    <p class="text-red-500 mt-4">
        You are not authorized to access admin features.
    </p>
{/if}