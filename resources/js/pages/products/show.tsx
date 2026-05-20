import { Head, Link } from '@inertiajs/react';
import { formatGHS } from '@/lib/constants';
import type { Product } from '@/types';
import { ArrowLeft, Pencil, Package } from 'lucide-react';

interface Props {
    product: Product;
}

export default function ProductShow({ product }: Props) {
    const isLow = product.quantity <= product.reorder_level;

    return (
        <>
            <Head title={product.name} />

            <div className="p-6">
                <Link
                    href="/products"
                    className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Products
                </Link>

                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">{product.name}</h1>
                        {product.part_number && (
                            <p className="text-sm text-muted-foreground">{product.part_number}</p>
                        )}
                    </div>
                    <Link
                        href={`/products/${product.id}/edit`}
                        className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                    >
                        <Pencil className="h-4 w-4" />
                        Edit
                    </Link>
                </div>

                <div className="mt-6 grid max-w-3xl grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Pricing Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Pricing
                        </h3>
                        <div className="mt-3 space-y-2">
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Cost Price</span>
                                <span className="text-sm font-semibold">{formatGHS(product.cost_price)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Selling Price</span>
                                <span className="text-sm font-semibold">{formatGHS(product.selling_price)}</span>
                            </div>
                            <div className="flex justify-between border-t pt-2">
                                <span className="text-sm text-muted-foreground">Margin</span>
                                <span className="text-sm font-semibold text-sabr-green">
                                    {formatGHS(product.selling_price - product.cost_price)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Stock Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Stock
                        </h3>
                        <div className="mt-3 space-y-2">
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Current Quantity</span>
                                <span className={`text-sm font-semibold ${
                                    product.quantity === 0 ? 'text-red-600' : isLow ? 'text-amber-600' : ''
                                }`}>
                                    {product.quantity} {product.unit}(s)
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Reorder Level</span>
                                <span className="text-sm">{product.reorder_level}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Status</span>
                                <span className={`inline-block rounded-full px-3 py-0.5 text-xs font-semibold ${
                                    product.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-600'
                                }`}>
                                    {product.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            {isLow && (
                                <div className="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                                    ⚠ Stock is at or below reorder level
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Details Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5 md:col-span-2">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Details
                        </h3>
                        <div className="mt-3 space-y-2">
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Category</span>
                                <span className="text-sm">{product.category?.name ?? '—'}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Supplier</span>
                                <span className="text-sm">{product.supplier?.name ?? '—'}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Unit</span>
                                <span className="text-sm capitalize">{product.unit}</span>
                            </div>
                            {product.description && (
                                <div className="border-t pt-2">
                                    <span className="text-sm text-muted-foreground">Description</span>
                                    <p className="mt-1 text-sm">{product.description}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

