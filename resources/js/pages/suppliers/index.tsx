import { Head, Link, router } from '@inertiajs/react';
import type { Supplier, PaginatedData } from '@/types';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
    suppliers: PaginatedData<Supplier>;
}

export default function SuppliersIndex({ suppliers }: Props) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredSuppliers = suppliers.data.filter((s) =>
        s.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (s.contact_person && s.contact_person.toLowerCase().includes(searchQuery.toLowerCase())),
    );

    function handleDelete(id: number, name: string) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            router.delete(`/suppliers/${id}`);
        }
    }

    return (
        <>
            <Head title="Suppliers" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Suppliers</h1>
                        <p className="text-sm text-muted-foreground">Manage your supplier network</p>
                    </div>
                    <Link href="/suppliers/create" className="inline-flex items-center gap-2 rounded-lg bg-sadiq-red px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90">
                        <Plus className="h-4 w-4" /> Add Supplier
                    </Link>
                </div>

                <div className="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input type="text" placeholder="Search by name or contact person..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground" />
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full">
                        <thead className="border-b bg-muted">
                            <tr>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Contact Person</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Phone</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Email</th>
                                <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredSuppliers.length > 0 ? (
                                filteredSuppliers.map((s) => (
                                    <tr key={s.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4 text-sm font-medium">{s.name}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{s.contact_person ?? '—'}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{s.phone ?? '—'}</td>
                                        <td className="p-4 text-sm text-muted-foreground">{s.email ?? '—'}</td>
                                        <td className="p-4">
                                            <div className="flex items-center gap-2">
                                                <Link href={`/suppliers/${s.id}/edit`} className="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                                <button onClick={() => handleDelete(s.id, s.name)} className="rounded p-1 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr><td colSpan={5} className="p-8 text-center text-sm text-muted-foreground">No suppliers found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
