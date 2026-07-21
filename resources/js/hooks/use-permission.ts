import { usePage } from '@inertiajs/react';
import type { Auth } from '@/types';

/**
 * Returns helpers to check the current user's permissions and roles.
 *
 * Usage:
 *   const { can, hasRole } = usePermission();
 *   if (can('edit-products')) { ... }
 *   if (hasRole('admin')) { ... }
 */
export function usePermission() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const permissions: string[] = (auth?.user?.permissions as string[]) ?? [];
    const roles: string[] = (auth?.user?.roles as string[]) ?? [];

    function can(permission: string): boolean {
        return permissions.includes(permission);
    }

    function canAny(...perms: string[]): boolean {
        return perms.some((p) => permissions.includes(p));
    }

    function hasRole(role: string): boolean {
        return roles.includes(role);
    }

    return { can, canAny, hasRole, permissions, roles };
}
