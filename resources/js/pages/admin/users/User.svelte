<script lang="ts">
    import { onMount } from 'svelte';

    import { Button } from '@/components/ui/button';
    import { Card } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Separator } from '@/components/ui/separator';
    import { Badge } from '@/components/ui/badge';
    import { Spinner } from '@/components/ui/spinner';

    export let userId: number;

    let user: any = null;
    let loading = true;
    let saving = false;

    // source of truth
    let roles: string[] = [];
    let roleError: string | null = null;

    /**
     * LOAD USER
     */
    async function load() {
        loading = true;

        const response = await fetch(`/api/v1/admin/users/${userId}`, {
            credentials: 'include',
        });

        const data = await response.json();

        user = data;
        roles = Array.isArray(data.roles) ? [...data.roles] : [];

        loading = false;
    }

    onMount(load);

    /**
     * TOGGLE ROLE (ONLY SOURCE OF TRUTH)
     */
    function toggleRole(role: string) {
        roleError = null;
        if (role === 'user') return;

        if (roles.includes(role)) {
            roles = roles.filter((r) => r !== role);
        } else {
            roles = [...roles, role];
        }
    }

    /**
     * SAVE ROLES
     */
    async function saveRoles() {
        if (!user) return;

        saving = true;
        roleError = null;

        try {
            const response = await fetch(`/api/v1/admin/users/${user.id}/roles`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    roles: roles.filter(r => r !== 'user'),
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                // 🔥 THIS is the key part
                roleError = 'Failed to update roles';

                if(response.status === 403){
                    roleError = data.message;
                    roles=user.roles;
                    return;
                }
            }

            // success → rehydrate state
            roles = Array.isArray(data.roles) ? [...data.roles] : [];
            user.roles = roles;
        } catch (e: any) {
            roleError = 'Network error while updating roles';
        } finally {
            saving = false;
        }
    }

    /**
     * SOFT DELETE
     */
    async function softDelete() {
        if (!user) return;

        saving = true;

        try {
            await fetch(`/api/v1/admin/users/${user.id}`, {
                method: 'DELETE',
                credentials: 'include',
            });

            await load();
        } finally {
            saving = false;
        }
    }

    /**
     * RESTORE
     */
    async function restoreUser() {
        if (!user) return;

        saving = true;

        try {
            await fetch(`/api/v1/admin/users/${user.id}/restore`, {
                method: 'POST',
                credentials: 'include',
            });

            await load();
        } finally {
            saving = false;
        }
    }
</script>

{#if loading}

    <div class="flex items-center gap-2 text-sm text-muted-foreground">
        <Spinner class="h-4 w-4" />
        Loading user...
    </div>

{:else if user}

    <div class="space-y-6 ml-3">

        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold">{user.name}</h1>
            <p class="text-sm text-muted-foreground">{user.email}</p>
        </div>

        <Card class="p-6 space-y-6">

            <!-- USER INFO -->
            <div class="space-y-1">
                <div class="text-xs uppercase text-muted-foreground">
                    User Info
                </div>

                <div class="text-sm space-y-1">
                    <div><strong>Name:</strong> {user.name}</div>
                    <div><strong>Email:</strong> {user.email}</div>
                </div>
            </div>

            <Separator />

            <!-- ROLES -->
            <div class="space-y-3">

                <div class="text-xs uppercase text-muted-foreground">
                    Roles
                </div>

                <!-- BASE ROLE -->
                <div class="flex items-center gap-2">
                    <Checkbox checked disabled />
                    <span class="text-sm">user (required)</span>
                </div>

                <!-- ADMIN ROLE -->
                <div class="flex items-center gap-2">
                    <Checkbox
                        checked={roles.includes('admin')}
                        onclick={() => toggleRole('admin')}
                        disabled={user.roles.includes('admin')}
                    />

                    <span class="text-sm">admin</span>

                    {#if user.roles.includes('admin')}
                        <Badge variant="secondary">active</Badge>
                    {/if}
                </div>

                <Button
                    type="button"
                    onclick={saveRoles}
                    disabled={saving}
                    class="mt-2"
                >
                    {#if saving}
                        <Spinner class="mr-2 h-4 w-4" />
                        Saving...
                    {:else}
                        Save Roles
                    {/if}
                </Button>
                {#if roleError}
                    <div class="mt-2 text-sm text-red-500">
                        {roleError}
                    </div>
                {/if}
            </div>

            <Separator />

            <!-- ACCOUNT STATUS -->
            <div class="space-y-3">

                <div class="text-xs uppercase text-muted-foreground">
                    Account Status
                </div>

                {#if user.deleted_at}

                    <div class="text-sm text-red-500">
                        Deleted at: {user.deleted_at}
                    </div>

                    <Button
                        type="button"
                        onclick={restoreUser}
                        disabled={saving}
                    >
                        Restore User
                    </Button>

                {:else}

                    <div class="text-sm text-green-600">
                        Active
                    </div>

                    <Button
                        type="button"
                        onclick={softDelete}
                        disabled={saving}
                    >
                        Delete User
                    </Button>

                {/if}

            </div>

        </Card>

        <div>
            <a href="/admin/users" class="text-sm hover:underline">
                ← Back to Users
            </a>
        </div>

    </div>

{/if}