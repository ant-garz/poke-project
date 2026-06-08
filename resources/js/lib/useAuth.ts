import { page } from '@inertiajs/svelte';

type Role = { name: string };

export function useAuth() {
    const user = () => page.props.auth?.user;

    const roles = () =>
        (user()?.roles as Role[] | undefined) ?? [];

    const hasRole = (role: string) =>
        roles().some((r) => r.name === role);

    const isAdmin = () => hasRole('admin');

    return {
        user,
        roles,
        hasRole,
        isAdmin,
    };
}