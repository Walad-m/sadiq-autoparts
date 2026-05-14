import { Head, useForm, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function CategoryCreate() {
    const form = useForm({ name: '', description: '' });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/categories');
    }

    return (
        <>
            <Head title="Create Category" />

            <div className="p-6">
                <Link href="/categories" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Categories
                </Link>

                <h1 className="font-display text-2xl font-bold">Create Category</h1>
                <p className="text-sm text-muted-foreground">Add a new product category.</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div>
                        <label className="block text-sm font-medium">Category Name *</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="e.g. Brake System" />
                        {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Description</label>
                        <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="Optional description..." />
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-sadiq-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90 disabled:opacity-50">
                            {form.processing ? 'Saving...' : 'Save Category'}
                        </button>
                        <Link href="/categories" className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}
