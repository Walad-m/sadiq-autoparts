import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Shield } from 'lucide-react';
import type { Role } from '@/types';

interface Props {
    roles: Role[];
}

export default function RolesIndex({ roles }: Props) {
    function handleDelete(role: Role) {
        if (role.users_count && role.users_count > 0) {
            alert(`Cannot delete "${role.name}" — ${role.users_count} user(s) are assigned to it.`);
            return;
        }
        if (confirm(`Delete role "${role.name}"? This cannot be undone.`)) {
            router.delete(`/admin/roles/${role.id}`);
        }
    }

    return (
        <>
            <Head title="Roles" />

            <div className="flex flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Roles</h1>
                        <p className="text-sm text-muted-foreground">
                            Define what each role can access. Assign roles to users in User Management.
                        </p>
                    </div>
                    <Link
                        href="/admin/roles/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                    >
                        <Plus className="h-4 w-4" />
                        Create Role
                    </Link>
                </div>

                {/* Roles grid */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {roles.map((role) => (
                        <div
                            key={role.id}
                            className="relative flex flex-col gap-3 rounded-xl border bg-card p-5 shadow-sm transition-shadow hover:shadow"
                        >
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-2">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-sabr-red/10">
                                        <Shield className="h-5 w-5 text-sabr-red" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold capitalize">{role.name}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {role.permissions_count} permission{role.permissions_count !== 1 ? 's' : ''}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex gap-1">
                                    <Link
                                        href={`/admin/roles/${role.id}/edit`}
                                        className="rounded p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        title="Edit role"
                                    >
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                    {role.name !== 'admin' && (
                                        <button
                                            onClick={() => handleDelete(role)}
                                            className="rounded p-1.5 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600"
                                            title="Delete role"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center justify-between border-t pt-3">
                                <span className="text-xs text-muted-foreground">
                                    {role.users_count ?? 0} user{(role.users_count ?? 0) !== 1 ? 's' : ''} assigned
                                </span>
                                {(role.users_count ?? 0) > 0 && (
                                    <span className="inline-block rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                        In use
                                    </span>
                                )}
                            </div>
                        </div>
                    ))}
                </div>

                {roles.length === 0 && (
                    <p className="text-center text-sm text-muted-foreground">No roles found.</p>
                )}
            </div>
        </>
    );
}
