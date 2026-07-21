import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Search, Filter } from 'lucide-react';
import type { ActivityLog, PaginatedData } from '@/types';

interface UserOption {
    id: number;
    name: string;
}

interface Props {
    logs: PaginatedData<ActivityLog>;
    users: UserOption[];
    eventTypes: string[];
    filters: {
        user_id?: string;
        event?: string;
        from?: string;
        to?: string;
    };
}

const EVENT_STYLES: Record<string, { bg: string; text: string; dot: string }> = {
    created: { bg: 'bg-green-100', text: 'text-green-700', dot: 'bg-green-500' },
    updated: { bg: 'bg-amber-100', text: 'text-amber-700', dot: 'bg-amber-500' },
    deleted: { bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-500' },
    deactivated: { bg: 'bg-gray-100', text: 'text-gray-600', dot: 'bg-gray-400' },
};

function getEventStyle(description: string) {
    for (const [key, style] of Object.entries(EVENT_STYLES)) {
        if (description.includes(key)) return style;
    }
    return { bg: 'bg-blue-100', text: 'text-blue-700', dot: 'bg-blue-500' };
}

function formatDescription(description: string) {
    return description.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatDateTime(dateStr: string) {
    const d = new Date(dateStr);
    return d.toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

export default function ActivityLogIndex({ logs, users, eventTypes, filters }: Props) {
    const [form, setForm] = useState({
        user_id: filters.user_id ?? '',
        event: filters.event ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    function applyFilters(e: React.FormEvent) {
        e.preventDefault();
        router.get('/admin/activity-log', form, { preserveState: true });
    }

    function clearFilters() {
        const cleared = { user_id: '', event: '', from: '', to: '' };
        setForm(cleared);
        router.get('/admin/activity-log', cleared);
    }

    const hasActiveFilters = Object.values(form).some(Boolean);

    return (
        <>
            <Head title="Activity Log" />

            <div className="flex flex-col gap-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="font-display text-2xl font-bold">Activity Log</h1>
                    <p className="text-sm text-muted-foreground">
                        A full audit trail of who did what and when across the system.
                    </p>
                </div>

                {/* Filters */}
                <form onSubmit={applyFilters} className="rounded-lg border bg-muted/30 p-4">
                    <div className="flex flex-wrap items-end gap-3">
                        <div className="flex-1 min-w-[160px]">
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">User</label>
                            <select
                                value={form.user_id}
                                onChange={(e) => setForm({ ...form, user_id: e.target.value })}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">All users</option>
                                {users.map((u) => (
                                    <option key={u.id} value={u.id}>{u.name}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex-1 min-w-[160px]">
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">Event</label>
                            <select
                                value={form.event}
                                onChange={(e) => setForm({ ...form, event: e.target.value })}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">All events</option>
                                {eventTypes.map((ev) => (
                                    <option key={ev} value={ev}>{formatDescription(ev)}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex-1 min-w-[130px]">
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">From</label>
                            <input
                                type="date"
                                value={form.from}
                                onChange={(e) => setForm({ ...form, from: e.target.value })}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            />
                        </div>

                        <div className="flex-1 min-w-[130px]">
                            <label className="mb-1 block text-xs font-medium text-muted-foreground">To</label>
                            <input
                                type="date"
                                value={form.to}
                                onChange={(e) => setForm({ ...form, to: e.target.value })}
                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            />
                        </div>

                        <div className="flex gap-2">
                            <button
                                type="submit"
                                className="inline-flex items-center gap-1.5 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                            >
                                <Filter className="h-3.5 w-3.5" />
                                Filter
                            </button>
                            {hasActiveFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="rounded-lg border border-input px-4 py-2 text-sm transition-colors hover:bg-muted"
                                >
                                    Clear
                                </button>
                            )}
                        </div>
                    </div>
                </form>

                {/* Log table */}
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">When</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Who</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Action</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.data.length > 0 ? (
                                logs.data.map((log) => {
                                    const style = getEventStyle(log.description);
                                    return (
                                        <tr key={log.id} className="border-t transition-colors hover:bg-muted/40">
                                            <td className="p-4 text-xs text-muted-foreground whitespace-nowrap">
                                                {formatDateTime(log.created_at)}
                                            </td>
                                            <td className="p-4">
                                                {log.causer ? (
                                                    <div className="flex items-center gap-2">
                                                        <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sabr-red/10 text-xs font-bold text-sabr-red">
                                                            {log.causer.name.charAt(0).toUpperCase()}
                                                        </div>
                                                        <span className="text-sm">{log.causer.name}</span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">System</span>
                                                )}
                                            </td>
                                            <td className="p-4">
                                                <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ${style.bg} ${style.text}`}>
                                                    <span className={`h-1.5 w-1.5 rounded-full ${style.dot}`} />
                                                    {formatDescription(log.description)}
                                                </span>
                                            </td>
                                            <td className="p-4 text-sm text-muted-foreground">
                                                {log.subject_type && log.subject_id
                                                    ? `${log.subject_type} #${log.subject_id}`
                                                    : '—'}
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan={4} className="p-8 text-center text-sm text-muted-foreground">
                                        No activity found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Showing {logs.from ?? 0}–{logs.to ?? 0} of {logs.total} entries
                    </span>
                    <div className="flex gap-2">
                        {logs.current_page > 1 && (
                            <Link
                                href={`/admin/activity-log?page=${logs.current_page - 1}`}
                                className="rounded-md border px-3 py-1 transition-colors hover:bg-muted"
                            >
                                Previous
                            </Link>
                        )}
                        {logs.current_page < logs.last_page && (
                            <Link
                                href={`/admin/activity-log?page=${logs.current_page + 1}`}
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
