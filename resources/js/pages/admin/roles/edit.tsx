import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { Role } from '@/types';

interface PermissionItem {
    id: number;
    name: string;
}

interface Props {
    role: Role & { permissions: string[] };
    groupedPermissions: Record<string, PermissionItem[]>;
}

function formatPermission(name: string) {
    return name.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function RoleEdit({ role, groupedPermissions }: Props) {
    const allPermissions = Object.values(groupedPermissions).flat().map((p) => p.name);

    const form = useForm<{ name: string; permissions: string[] }>({
        name: role.name,
        permissions: role.permissions ?? [],
    });

    function togglePermission(permName: string) {
        const current = form.data.permissions;
        if (current.includes(permName)) {
            form.setData('permissions', current.filter((p) => p !== permName));
        } else {
            form.setData('permissions', [...current, permName]);
        }
    }

    function toggleGroup(perms: PermissionItem[]) {
        const names = perms.map((p) => p.name);
        const allChecked = names.every((n) => form.data.permissions.includes(n));
        if (allChecked) {
            form.setData('permissions', form.data.permissions.filter((p) => !names.includes(p)));
        } else {
            const merged = Array.from(new Set([...form.data.permissions, ...names]));
            form.setData('permissions', merged);
        }
    }

    function selectAll() { form.setData('permissions', allPermissions); }
    function deselectAll() { form.setData('permissions', []); }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(`/admin/roles/${role.id}`);
    }

    return (
        <>
            <Head title={`Edit Role — ${role.name}`} />

            <div className="p-6">
                <Link
                    href="/admin/roles"
                    className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Roles
                </Link>

                <h1 className="font-display text-2xl font-bold capitalize">Edit Role — {role.name}</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Rename the role or change its permissions. Changes take effect on the user's next page load.
                </p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    {/* Role Name */}
                    <div className="max-w-sm">
                        <label className="block text-sm font-medium">Role Name *</label>
                        <input
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                        />
                        {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                    </div>

                    {/* Permissions */}
                    <div>
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="text-sm font-semibold">
                                Permissions
                                <span className="ml-2 text-xs font-normal text-muted-foreground">
                                    ({form.data.permissions.length} selected)
                                </span>
                            </h2>
                            <div className="flex gap-3 text-xs">
                                <button type="button" onClick={selectAll} className="text-sabr-red hover:underline">
                                    Select all
                                </button>
                                <button type="button" onClick={deselectAll} className="text-muted-foreground hover:underline">
                                    Deselect all
                                </button>
                            </div>
                        </div>

                        <div className="space-y-4">
                            {Object.entries(groupedPermissions).map(([group, perms]) => {
                                const allChecked = perms.every((p) => form.data.permissions.includes(p.name));
                                const someChecked = perms.some((p) => form.data.permissions.includes(p.name));

                                return (
                                    <div key={group} className="rounded-lg border p-4">
                                        <div className="mb-3 flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                id={`group-${group}`}
                                                checked={allChecked}
                                                ref={(el) => { if (el) el.indeterminate = someChecked && !allChecked; }}
                                                onChange={() => toggleGroup(perms)}
                                                className="h-4 w-4 rounded border-gray-300"
                                            />
                                            <label htmlFor={`group-${group}`} className="text-sm font-semibold">
                                                {group}
                                            </label>
                                        </div>
                                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                            {perms.map((perm) => (
                                                <label key={perm.id} className="flex cursor-pointer items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        checked={form.data.permissions.includes(perm.name)}
                                                        onChange={() => togglePermission(perm.name)}
                                                        className="h-4 w-4 rounded border-gray-300"
                                                    />
                                                    <span className="text-xs">{formatPermission(perm.name)}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Submit */}
                    <div className="flex gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-sabr-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90 disabled:opacity-50"
                        >
                            {form.processing ? 'Saving…' : 'Save Changes'}
                        </button>
                        <Link
                            href="/admin/roles"
                            className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}
