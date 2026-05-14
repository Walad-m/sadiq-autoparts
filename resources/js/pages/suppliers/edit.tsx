import { Head, useForm, Link } from '@inertiajs/react';
import type { Supplier } from '@/types';
import { ArrowLeft } from 'lucide-react';

interface Props {
    supplier: Supplier;
}

export default function SupplierEdit({ supplier }: Props) {
    const form = useForm({
        name: supplier.name ?? '',
        contact_person: supplier.contact_person ?? '',
        phone: supplier.phone ?? '',
        email: supplier.email ?? '',
        address: supplier.address ?? '',
        notes: supplier.notes ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(`/suppliers/${supplier.id}`);
    }

    return (
        <>
            <Head title={`Edit: ${supplier.name}`} />

            <div className="p-6">
                <Link href="/suppliers" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Suppliers
                </Link>

                <h1 className="font-display text-2xl font-bold">Edit Supplier</h1>
                <p className="text-sm text-muted-foreground">Update supplier information.</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Company Name *</label>
                            <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                            {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Contact Person</label>
                            <input value={form.data.contact_person} onChange={(e) => form.setData('contact_person', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Phone</label>
                            <input type="tel" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Email</label>
                            <input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Address</label>
                        <input value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Notes</label>
                        <textarea value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-sadiq-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90 disabled:opacity-50">
                            {form.processing ? 'Saving...' : 'Update Supplier'}
                        </button>
                        <Link href="/suppliers" className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium transition-colors hover:bg-muted">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}
