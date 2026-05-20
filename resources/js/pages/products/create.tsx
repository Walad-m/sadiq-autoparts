import { Head, useForm, Link } from '@inertiajs/react';
import type { Category, Supplier } from '@/types';
import { PRODUCT_UNITS } from '@/lib/constants';
import { ArrowLeft } from 'lucide-react';

interface Props {
    categories: Category[];
    suppliers: Supplier[];
}

export default function ProductCreate({ categories, suppliers }: Props) {
    const form = useForm({
        name: '',
        description: '',
        part_number: '',
        category_id: '',
        supplier_id: '',
        unit: 'piece' as string,
        cost_price: '',
        selling_price: '',
        quantity: '0',
        reorder_level: '5',
        is_active: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/products');
    }

    return (
        <>
            <Head title="Create Product" />

            <div className="p-6">
                <Link
                    href="/products"
                    className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Products
                </Link>

                <h1 className="font-display text-2xl font-bold">Create Product</h1>
                <p className="text-sm text-muted-foreground">Add a new product to your catalogue.</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-medium">Name *</label>
                        <input
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            placeholder="e.g. Oil Filter — Toyota Corolla"
                        />
                        {form.errors.name && (
                            <p className="mt-1 text-sm text-red-500">{form.errors.name}</p>
                        )}
                    </div>

                    {/* Part Number + Unit */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Part Number</label>
                            <input
                                value={form.data.part_number}
                                onChange={(e) => form.setData('part_number', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                placeholder="Manufacturer reference"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Unit *</label>
                            <select
                                value={form.data.unit}
                                onChange={(e) => form.setData('unit', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            >
                                {PRODUCT_UNITS.map((u) => (
                                    <option key={u.value} value={u.value}>
                                        {u.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Category + Supplier */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Category *</label>
                            <select
                                value={form.data.category_id}
                                onChange={(e) => form.setData('category_id', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Select category</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                            {form.errors.category_id && (
                                <p className="mt-1 text-sm text-red-500">{form.errors.category_id}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Supplier</label>
                            <select
                                value={form.data.supplier_id}
                                onChange={(e) => form.setData('supplier_id', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">No supplier</option>
                                {suppliers.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Cost Price + Selling Price */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Cost Price (GHS) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                value={form.data.cost_price}
                                onChange={(e) => form.setData('cost_price', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                placeholder="0.00"
                            />
                            {form.errors.cost_price && (
                                <p className="mt-1 text-sm text-red-500">{form.errors.cost_price}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Selling Price (GHS) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                value={form.data.selling_price}
                                onChange={(e) => form.setData('selling_price', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                placeholder="0.00"
                            />
                            {form.errors.selling_price && (
                                <p className="mt-1 text-sm text-red-500">{form.errors.selling_price}</p>
                            )}
                        </div>
                    </div>

                    {/* Quantity + Reorder Level */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Current Stock *</label>
                            <input
                                type="number"
                                min="0"
                                value={form.data.quantity}
                                onChange={(e) => form.setData('quantity', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            />
                            {form.errors.quantity && (
                                <p className="mt-1 text-sm text-red-500">{form.errors.quantity}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Reorder Level *</label>
                            <input
                                type="number"
                                min="0"
                                value={form.data.reorder_level}
                                onChange={(e) => form.setData('reorder_level', e.target.value)}
                                className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            />
                            {form.errors.reorder_level && (
                                <p className="mt-1 text-sm text-red-500">{form.errors.reorder_level}</p>
                            )}
                        </div>
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-medium">Description</label>
                        <textarea
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            rows={3}
                            className="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                            placeholder="Optional description..."
                        />
                    </div>

                    {/* Active toggle */}
                    <div className="flex items-center gap-3">
                        <input
                            type="checkbox"
                            id="is_active"
                            checked={form.data.is_active}
                            onChange={(e) => form.setData('is_active', e.target.checked)}
                            className="h-4 w-4 rounded border-gray-300"
                        />
                        <label htmlFor="is_active" className="text-sm font-medium">
                            Active (visible in POS)
                        </label>
                    </div>

                    {/* Submit */}
                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-sabr-red px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90 disabled:opacity-50"
                        >
                            {form.processing ? 'Saving...' : 'Save Product'}
                        </button>
                        <Link
                            href="/products"
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

