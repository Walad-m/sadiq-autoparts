import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Plus, Pencil, Search, UserX, UserCheck, Trash2 } from 'lucide-react';
import type { UserRecord, PaginatedData } from '@/types';

interface Props {
    users: PaginatedData<UserRecord>;
    filters: { search?: string };
}

const ROLE_COLORS: Record<string, string> = {
    admin: 'bg-red-100 text-red-700',
    manager: 'bg-purple-100 text-purple-700',
    cashier: 'bg-blue-100 text-blue-700',
    'inventory clerk': 'bg-amber-100 text-amber-700',
    accountant: 'bg-green-100 text-green-700',
};

export default function UsersIndex({ users, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get('/admin/users', { search }, { preserveState: true });
    }

    function handleDeactivate(user: UserRecord) {
        if (confirm(`Deactivate "${user.name}"? They will no longer be able to log in.`)) {
            router.delete(`/admin/users/${user.id}`);
        }
    }

    function handleActivate(user: UserRecord) {
        if (confirm(`Reactivate "${user.name}"?`)) {
            router.put(`/admin/users/${user.id}`, { ...user, is_active: true });
        }
    }

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    }

    return (
        <>
            <Head title="User Management" />

            <div className="flex flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">User Management</h1>
                        <p className="text-sm text-muted-foreground">
                            Add staff, assign roles, and control access to the system.
                        </p>
                    </div>
                    <Link
                        href="/admin/users/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                    >
                        <Plus className="h-4 w-4" />
                        Add User
                    </Link>
                </div>

                {/* Search */}
                <form onSubmit={handleSearch} className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input
                        type="text"
                        placeholder="Search by name or email..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    />
                    <button type="submit" className="text-xs text-muted-foreground hover:text-foreground">
                        Search
                    </button>
                </form>

                {/* Table */}
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Email</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Role</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Added</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.length > 0 ? (
                                users.data.map((user) => (
                                    <tr key={user.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sabr-red/10 text-sm font-bold text-sabr-red">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </div>
                                                <span className="text-sm font-medium">{user.name}</span>
                                            </div>
                                        </td>
                                        <td className="p-4 text-sm text-muted-foreground">{user.email}</td>
                                        <td className="p-4">
                                            <div className="flex flex-wrap gap-1">
                                                {user.roles.length > 0 ? (
                                                    user.roles.map((role) => (
                                                        <span
                                                            key={role}
                                                            className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ${ROLE_COLORS[role] ?? 'bg-gray-100 text-gray-700'}`}
                                                        >
                                                            {role}
                                                        </span>
                                                    ))
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">No role</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="p-4">
                                            <span
                                                className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                    user.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-gray-100 text-gray-500'
                                                }`}
                                            >
                                                {user.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                        <td className="p-4 text-sm text-muted-foreground">{formatDate(user.created_at)}</td>
                                        <td className="p-4">
                                            <div className="flex items-center gap-1">
                                                <Link
                                                    href={`/admin/users/${user.id}/edit`}
                                                    className="rounded p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                                    title="Edit user"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                                {user.is_active ? (
                                                    <button
                                                        onClick={() => handleDeactivate(user)}
                                                        className="rounded p-1.5 text-muted-foreground transition-colors hover:bg-amber-50 hover:text-amber-600"
                                                        title="Deactivate user"
                                                    >
                                                        <UserX className="h-4 w-4" />
                                                    </button>
                                                ) : (
                                                    <button
                                                        onClick={() => handleActivate(user)}
                                                        className="rounded p-1.5 text-muted-foreground transition-colors hover:bg-green-50 hover:text-green-600"
                                                        title="Reactivate user"
                                                    >
                                                        <UserCheck className="h-4 w-4" />
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={6} className="p-8 text-center text-sm text-muted-foreground">
                                        No users found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Showing {users.from ?? 0}–{users.to ?? 0} of {users.total} users
                    </span>
                    <div className="flex gap-2">
                        {users.current_page > 1 && (
                            <Link
                                href={`/admin/users?page=${users.current_page - 1}`}
                                className="rounded-md border px-3 py-1 transition-colors hover:bg-muted"
                            >
                                Previous
                            </Link>
                        )}
                        {users.current_page < users.last_page && (
                            <Link
                                href={`/admin/users?page=${users.current_page + 1}`}
                                className="rounded-md border px-3 py-1 transition-colors hover:bg-muted"
                            >
                                Next
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
