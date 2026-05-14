import { Head, useForm, Link } from '@inertiajs/react';
import type { Category, Supplier, Product } from '@/types';
import { PRODUCT_UNITS } from '@/lib/constants';
import { ArrowLeft } from 'lucide-react';

interface Props {
    product: Product;
    categories: Category[];
    suppliers: Supplier[];
}

export default function ProductEdit({ product, categories, suppliers }: Props) {
    const form = useForm({
        name: product.name ?? '',
        description: product.description ?? '',
        part_number: product.part_number ?? '',
        category_id: String(product.category_id ?? ''),
        supplier_id: String(product.supplier_id ?? ''),
        unit: (product.unit ?? 'piece') as string,
        cost_price: String(product.cost_price ?? ''),
        selling_price: String(product.selling_price ?? ''),
        quantity: String(product.quantity ?? '0'),
        reorder_level: String(product.reorder_level ?? '5'),
        is_active: product.is_active ?? true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.put(`/products/${product.id}`);
    }

    return (
        <>
            <Head title={`Edit: ${product.name}`} />
            <div className="p-6">
                <Link href="/products" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Products
                </Link>
                <h1 className="font-display text-2xl font-bold">Edit Product</h1>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div>
                        <label className="block text-sm font-medium">Name *</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        {form.errors.name && <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Part Number</label>
                            <input value={form.data.part_number} onChange={(e) => form.setData('part_number', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Unit *</label>
                            <select value={form.data.unit} onChange={(e) => form.setData('unit', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                {PRODUCT_UNITS.map((u) => (<option key={u.value} value={u.value}>{u.label}</option>))}
                            </select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Category *</label>
                            <select value={form.data.category_id} onChange={(e) => form.setData('category_id', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                <option value="">Select category</option>
                                {categories.map((c) => (<option key={c.id} value={c.id}>{c.name}</option>))}
                            </select>
                            {form.errors.category_id && <p className="mt-1 text-sm text-red-500">{form.errors.category_id}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Supplier</label>
                            <select value={form.data.supplier_id} onChange={(e) => form.setData('supplier_id', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                <option value="">No supplier</option>
                                {suppliers.map((s) => (<option key={s.id} value={s.id}>{s.name}</option>))}
                            </select>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Cost Price (GHS) *</label>
                            <input type="number" step="0.01" min="0" value={form.data.cost_price} onChange={(e) => form.setData('cost_price', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                            {form.errors.cost_price && <p className="mt-1 text-sm text-red-500">{form.errors.cost_price}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Selling Price (GHS) *</label>
                            <input type="number" step="0.01" min="0" value={form.data.selling_price} onChange={(e) => form.setData('selling_price', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                            {form.errors.selling_price && <p className="mt-1 text-sm text-red-500">{form.errors.selling_price}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Current Stock *</label>
                            <input type="number" min="0" value={form.data.quantity} onChange={(e) => form.setData('quantity', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Reorder Level *</label>
                            <input type="number" min="0" value={form.data.reorder_level} onChange={(e) => form.setData('reorder_level', e.target.value)} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Description</label>
                        <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={3} className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                    </div>

                    <div className="flex items-center gap-3">
                        <input type="checkbox" id="is_active" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-gray-300" />
                        <label htmlFor="is_active" className="text-sm font-medium">Active (visible in POS)</label>
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing} className="rounded-lg bg-sadiq-red px-6 py-2.5 text-sm font-semibold text-white hover:bg-sadiq-red/90 disabled:opacity-50">
                            {form.processing ? 'Saving...' : 'Update Product'}
                        </button>
                        <Link href="/products" className="rounded-lg border border-input px-6 py-2.5 text-sm font-medium hover:bg-muted">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}
