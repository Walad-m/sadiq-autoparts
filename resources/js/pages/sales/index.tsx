import { Head, Link, router } from '@inertiajs/react';
import { formatGHS, formatSimpleDate } from '@/lib/constants';
import type { Sale, PaginatedData } from '@/types';
import { Search, Monitor, Eye, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
    sales: PaginatedData<Sale>;
}

export default function SalesIndex({ sales }: Props) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredSales = sales.data.filter((s) =>
        (s.sale_number && s.sale_number.toLowerCase().includes(searchQuery.toLowerCase())) ||
        (s.customer?.name && s.customer.name.toLowerCase().includes(searchQuery.toLowerCase())),
    );

    function handleDelete(id: number, saleNumber: string) {
        if (confirm(`Are you sure you want to delete sale "${saleNumber}"?`)) {
            router.delete(`/sales/${id}`);
        }
    }

    return (
        <>
            <Head title="Sales History" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Sales History</h1>
                        <p className="text-sm text-muted-foreground">View all completed sales transactions</p>
                    </div>
                    <Link
                        href="/pos"
                        className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                    >
                        <Monitor className="h-4 w-4" />
                        Open POS
                    </Link>
                </div>

                <div className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input type="text" placeholder="Search by sale number or customer..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground" />
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Sale #</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Customer</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Total</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Payment</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Cashier</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Date</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredSales.length > 0 ? (
                                filteredSales.map((s) => (
                                    <tr key={s.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4 text-sm font-mono font-medium">{s.sale_number}</td>
                                        <td className="p-4 text-sm">{s.customer?.name ?? 'Walk-in'}</td>
                                        <td className="p-4 text-sm font-semibold">{formatGHS(s.total)}</td>
                                        <td className="p-4 text-sm">
                                            <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${s.payment_method === 'momo' ? 'bg-sabr-teal/10 text-sabr-teal' : 'bg-sabr-green/10 text-sabr-green'}`}>
                                                {s.payment_method === 'momo' ? 'MoMo' : 'Cash'}
                                            </span>
                                        </td>
                                        <td className="p-4 text-sm">
                                            <span className={`inline-block rounded-full px-3 py-0.5 text-xs font-semibold ${s.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {s.status}
                                            </span>
                                        </td>
                                        <td className="p-4 text-sm text-muted-foreground">{s.user?.name ?? '—'}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{formatSimpleDate(s.created_at)}</td>
                                        <td className="p-4">
                                            <div className="flex items-center gap-2">
                                                <Link href={`/sales/${s.id}`} className="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                                <button onClick={() => handleDelete(s.id, s.sale_number)} className="rounded p-1 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={8} className="p-8 text-center text-sm text-muted-foreground">
                                        No sales found. <Link href="/pos" className="text-sabr-red hover:underline">Open POS</Link> to make a sale.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>Showing {sales.from ?? 0}–{sales.to ?? 0} of {sales.total} sales</span>
                    <div className="flex gap-2">
                        {sales.current_page > 1 && (
                            <Link href={`/sales?page=${sales.current_page - 1}`} className="rounded-md border px-3 py-1 transition-colors hover:bg-muted">Previous</Link>
                        )}
                        {sales.current_page < sales.last_page && (
                            <Link href={`/sales?page=${sales.current_page + 1}`} className="rounded-md border px-3 py-1 transition-colors hover:bg-muted">Next</Link>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

