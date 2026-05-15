import { Head, Link, router } from '@inertiajs/react';
import { formatGHS } from '@/lib/constants';
import type { Expense, PaginatedData } from '@/types';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ResourceImportExport from '@/components/resource-import-export';

interface Props {
    expenses: PaginatedData<Expense>;
}

export default function ExpensesIndex({ expenses }: Props) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredExpenses = expenses.data.filter((e) =>
        e.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        e.category.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    function handleDelete(id: number, title: string) {
        if (confirm(`Are you sure you want to delete "${title}"?`)) {
            router.delete(`/expenses/${id}`);
        }
    }

    return (
        <>
            <Head title="Expenses" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Expenses</h1>
                        <p className="text-sm text-muted-foreground">Track and manage business expenses</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <ResourceImportExport
                            exportUrl="/expenses/export"
                            importUrl="/expenses/import"
                            entityName="expenses"
                        />
                        <Link href="/expenses/create" className="inline-flex items-center gap-2 rounded-lg bg-sadiq-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90">
                            <Plus className="h-4 w-4" /> Add Expense
                        </Link>
                    </div>
                </div>

                <div className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input type="text" placeholder="Search by title or category..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground" />
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Title</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Category</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Amount</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Payment</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Date</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredExpenses.length > 0 ? (
                                filteredExpenses.map((e) => (
                                    <tr key={e.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4 text-sm font-medium">{e.title}</td>
                                        <td className="p-4 text-sm capitalize text-muted-foreground">{e.category}</td>
                                        <td className="p-4 text-sm font-semibold">{formatGHS(e.amount)}</td>
                                        <td className="p-4 text-sm">
                                            <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${e.payment_method === 'momo' ? 'bg-sadiq-teal/10 text-sadiq-teal' : 'bg-sadiq-green/10 text-sadiq-green'}`}>
                                                {e.payment_method === 'momo' ? 'MoMo' : 'Cash'}
                                            </span>
                                        </td>
                                        <td className="p-4 text-sm text-muted-foreground">{e.expense_date}</td>
                                        <td className="p-4">
                                            <div className="flex items-center gap-2">
                                                <Link href={`/expenses/${e.id}/edit`} className="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                                <button onClick={() => handleDelete(e.id, e.title)} className="rounded p-1 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr><td colSpan={6} className="p-8 text-center text-sm text-muted-foreground">No expenses found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
