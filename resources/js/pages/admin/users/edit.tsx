import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { Role, UserRecord } from '@/types';

interface Props {
    user: UserRecord & { roles: string[] };
    roles: Role[];
}

export default function UserEdit({ user, roles }: Props) {
    const form = useForm({
        name: user.name,
        email: user.email,
        password: '',
        role: user.roles[0] ?? '',
        is_active: user.is_active,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(`/admin/users/${user.id}`);
    }

    return (
        <>
            <Head title={`Edit User — ${user.name}`} />

            <div className="p-6">
                <Link
                    href="/admin/users"
                    className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Users
                </Link>

                <h1 className="font-display text-2xl font-bold">Edit User</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Update account details and role for <strong>{user.name}</strong>.
                </p>

                <form onSubmit={submit} className="mt-6 max-w-lg space-y-5">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-medium">Full Name *</label>
                        <input
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                        />
                        {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                    </div>

                    {/* Email */}
                    <div>
                        <label className="block text-sm font-medium">Email Address *</label>
                        <input
                            type="email"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                        />
                        {form.errors.email && <p className="mt-1 text-sm text-red-500">{form.errors.email}</p>}
                    </div>

                    {/* Password (optional) */}
                    <div>
                        <label className="block text-sm font-medium">New Password</label>
                        <input
                            type="password"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            placeholder="Leave blank to keep current password"
                        />
                        {form.errors.password && <p className="mt-1 text-sm text-red-500">{form.errors.password}</p>}
                    </div>

                    {/* Role */}
                    <div>
                        <label className="block text-sm font-medium">Role *</label>
                        <select
                            value={form.data.role}
                            onChange={(e) => form.setData('role', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm capitalize"
                        >
                            <option value="">Select a role…</option>
                            {roles.map((r) => (
                                <option key={r.id} value={r.name} className="capitalize">
                                    {r.name}
                                </option>
                            ))}
                        </select>
                        {form.errors.role && <p className="mt-1 text-sm text-red-500">{form.errors.role}</p>}
                    </div>

                    {/* Active */}
                    <div className="flex items-center gap-3">
                        <input
                            type="checkbox"
                            id="is_active"
                            checked={form.data.is_active}
                            onChange={(e) => form.setData('is_active', e.target.checked)}
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        <label htmlFor="is_active" className="text-sm font-medium">
                            Active (can log in)
                        </label>
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
                            href="/admin/users"
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
