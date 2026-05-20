import { Head, useForm, Link } from '@inertiajs/react';
import { EXPENSE_CATEGORIES, PAYMENT_METHODS } from '@/lib/constants';
import { ArrowLeft } from 'lucide-react';

export default function ExpenseCreate() {
    const form = useForm({
        title: '',
        amount: '',
        category: '' as string,
        payment_method: 'cash' as string,
        expense_date: new Date().toISOString().split('T')[0],
        notes: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/expenses');
    }

    return (
        <>
            <Head title="Create Expense" />

            <div className="p-6">
                <Link href="/expenses" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Expenses
                </Link>

                <h1 className="font-display text-2xl font-bold">Record Expense</h1>
                <p className="text-sm text-muted-foreground">Record a new business expense.</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div>
                        <label className="block text-sm font-medium">Title *</label>
                        <input value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="e.g. Electricity bill — May 2026" />
                        {form.errors.title && <p className="mt-1 text-sm text-red-500">{form.errors.title}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Category *</label>
                            <select value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                <option value="">Select category</option>
                                {EXPENSE_CATEGORIES.map((c) => (<option key={c.value} value={c.value}>{c.label}</option>))}
                            </select>
                            {form.errors.category && <p className="mt-1 text-sm text-red-500">{form.errors.category}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Payment Method *</label>
                            <select value={form.data.payment_method} onChange={(e) => form.setData('payment_method', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                {PAYMENT_METHODS.map((m) => (<option key={m.value} value={m.value}>{m.label}</option>))}
                            </select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Amount (GHS) *</label>
                            <input type="number" step="0.01" min="0" value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="0.00" />
                            {form.errors.amount && <p className="mt-1 text-sm text-red-500">{form.errors.amount}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Date *</label>
                            <input type="date" value={form.data.expense_date} onChange={(e) => form.setData('expense_date', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                            {form.errors.expense_date && <p className="mt-1 text-sm text-red-500">{form.errors.expense_date}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Notes</label>
                        <textarea value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="Optional notes..." />
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-sabr-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90 disabled:opacity-50">
                            {form.processing ? 'Saving...' : 'Save Expense'}
                        </button>
                        <Link href="/expenses" className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}

