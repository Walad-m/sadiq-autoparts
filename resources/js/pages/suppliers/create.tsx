import { Head, useForm, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function SupplierCreate() {
    const form = useForm({ name: '', contact_person: '', phone: '', email: '', address: '', notes: '' });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/suppliers');
    }

    return (
        <>
            <Head title="Create Supplier" />

            <div className="p-6">
                <Link href="/suppliers" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Suppliers
                </Link>

                <h1 className="font-display text-2xl font-bold">Create Supplier</h1>
                <p className="text-sm text-muted-foreground">Add a new supplier to your network.</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Company Name *</label>
                            <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="e.g. Abossey Okai Wholesale" />
                            {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Contact Person</label>
                            <input value={form.data.contact_person} onChange={(e) => form.setData('contact_person', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="Kwame Mensah" />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Phone</label>
                            <input type="tel" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="0244 123456" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Email</label>
                            <input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="info@supplier.com" />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Address</label>
                        <input value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="Abossey Okai, Accra" />
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Notes</label>
                        <textarea value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" placeholder="Optional notes..." />
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-sabr-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90 disabled:opacity-50">
                            {form.processing ? 'Saving...' : 'Save Supplier'}
                        </button>
                        <Link href="/suppliers" className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}

