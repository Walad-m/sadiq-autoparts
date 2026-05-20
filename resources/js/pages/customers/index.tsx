import { Head, Link, router } from '@inertiajs/react';
import type { Customer, PaginatedData } from '@/types';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ResourceImportExport from '@/components/resource-import-export';

interface Props {
    customers: PaginatedData<Customer>;
}

export default function CustomersIndex({ customers }: Props) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredCustomers = customers.data.filter((c) =>
        c.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (c.phone && c.phone.includes(searchQuery)),
    );

    function handleDelete(id: number, name: string) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            router.delete(`/customers/${id}`);
        }
    }

    return (
        <>
            <Head title="Customers" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Customers</h1>
                        <p className="text-sm text-muted-foreground">Manage your customer database</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <ResourceImportExport
                            exportUrl="/customers/export"
                            importUrl="/customers/import"
                            entityName="customers"
                        />
                        <Link
                            href="/customers/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                        >
                            <Plus className="h-4 w-4" />
                            Add Customer
                        </Link>
                    </div>
                </div>

                <div className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input
                        type="text"
                        placeholder="Search by name or phone..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    />
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Phone</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Email</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Address</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredCustomers.length > 0 ? (
                                filteredCustomers.map((c) => (
                                    <tr key={c.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4 text-sm font-medium">{c.name}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{c.phone ?? '—'}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{c.email ?? '—'}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{c.address ?? '—'}</td>
                                        <td className="p-4">
                                            <div className="flex items-center gap-2">
                                                <Link href={`/customers/${c.id}/edit`} className="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                                <button onClick={() => handleDelete(c.id, c.name)} className="rounded p-1 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={5} className="p-8 text-center text-sm text-muted-foreground">No customers found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

