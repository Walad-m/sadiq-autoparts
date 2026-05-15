import { Head, Link, router } from '@inertiajs/react';
import { formatGHS } from '@/lib/constants';
import type { Product, PaginatedData } from '@/types';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ResourceImportExport from '@/components/resource-import-export';

interface Props {
    products: PaginatedData<Product>;
}

export default function ProductsIndex({ products }: Props) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredProducts = products.data.filter(
        (p) =>
            p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            (p.part_number && p.part_number.toLowerCase().includes(searchQuery.toLowerCase())),
    );

    function handleDelete(id: number, name: string) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            router.delete(`/products/${id}`);
        }
    }

    return (
        <>
            <Head title="Products" />

            <div className="flex flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Products</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage your product catalogue and stock levels
                        </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <ResourceImportExport
                            exportUrl="/products/export"
                            importUrl="/products/import"
                            entityName="products"
                        />
                        <Link
                            href="/products/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-sadiq-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90"
                        >
                            <Plus className="h-4 w-4" />
                            Add Product
                        </Link>
                    </div>
                </div>

                {/* Search */}
                <div className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input
                        type="text"
                        placeholder="Search by name or part number..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    />
                </div>

                {/* Table */}
                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Name
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Category
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Stock
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Unit
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Sell Price
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Status
                                </th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredProducts.length > 0 ? (
                                filteredProducts.map((product) => {
                                    const isLow = product.quantity <= product.reorder_level;
                                    return (
                                        <tr
                                            key={product.id}
                                            className={`border-t transition-colors hover:bg-muted/50 ${
                                                isLow ? 'border-l-4 border-l-red-500' : ''
                                            }`}
                                        >
                                            <td className="p-4">
                                                <div className="text-sm font-medium">{product.name}</div>
                                                {product.part_number && (
                                                    <div className="text-xs text-muted-foreground">
                                                        {product.part_number}
                                                    </div>
                                                )}
                                            </td>
                                            <td className="p-4 text-sm">
                                                {product.category?.name ?? '—'}
                                            </td>
                                            <td className="p-4">
                                                <span
                                                    className={`text-sm font-semibold ${
                                                        product.quantity === 0
                                                            ? 'text-red-600'
                                                            : isLow
                                                              ? 'text-amber-600'
                                                              : ''
                                                    }`}
                                                >
                                                    {product.quantity}
                                                </span>
                                            </td>
                                            <td className="p-4 text-sm capitalize">{product.unit}</td>
                                            <td className="p-4 text-sm font-semibold">
                                                {formatGHS(product.selling_price)}
                                            </td>
                                            <td className="p-4 text-sm">
                                                <span
                                                    className={`inline-block rounded-full px-3 py-1 text-xs font-semibold ${
                                                        product.is_active
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-gray-100 text-gray-600'
                                                    }`}
                                                >
                                                    {product.is_active ? 'Active' : 'Inactive'}
                                                </span>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center gap-2">
                                                    <Link
                                                        href={`/products/${product.id}/edit`}
                                                        className="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(product.id, product.name)}
                                                        className="rounded p-1 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-8 text-center text-sm text-muted-foreground"
                                    >
                                        No products found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Showing {products.from ?? 0}–{products.to ?? 0} of {products.total} products
                    </span>
                    <div className="flex gap-2">
                        {products.current_page > 1 && (
                            <Link
                                href={`/products?page=${products.current_page - 1}`}
                                className="rounded-md border px-3 py-1 transition-colors hover:bg-muted"
                            >
                                Previous
                            </Link>
                        )}
                        {products.current_page < products.last_page && (
                            <Link
                                href={`/products?page=${products.current_page + 1}`}
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
