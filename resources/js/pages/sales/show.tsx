import { Head, Link } from '@inertiajs/react';
import { formatGHS } from '@/lib/constants';
import type { Sale } from '@/types';
import { ArrowLeft, Printer } from 'lucide-react';

interface Props {
    sale: Sale;
}

export default function SaleShow({ sale }: Props) {
    return (
        <>
            <Head title={`Sale ${sale.sale_number}`} />

            <div className="p-6">
                <Link href="/sales" className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                    <ArrowLeft className="h-4 w-4" /> Back to Sales
                </Link>

                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Sale {sale.sale_number}</h1>
                        <p className="text-sm text-muted-foreground">
                            {sale.created_at?.split('T')[0]} · {sale.user?.name ?? 'Unknown cashier'}
                        </p>
                    </div>
                    <button
                        onClick={() => window.print()}
                        className="inline-flex items-center gap-2 rounded-lg border border-input px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        <Printer className="h-4 w-4" /> Print Receipt
                    </button>
                </div>

                <div className="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    {/* Summary Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Summary</h3>
                        <div className="mt-3 space-y-2">
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Customer</span>
                                <span className="text-sm font-medium">{sale.customer?.name ?? 'Walk-in'}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Payment</span>
                                <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${sale.payment_method === 'momo' ? 'bg-sadiq-teal/10 text-sadiq-teal' : 'bg-sadiq-green/10 text-sadiq-green'}`}>
                                    {sale.payment_method === 'momo' ? 'MoMo' : 'Cash'}
                                </span>
                            </div>
                            {sale.momo_reference && (
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">MoMo Ref</span>
                                    <span className="text-sm font-mono">{sale.momo_reference}</span>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Status</span>
                                <span className={`inline-block rounded-full px-3 py-0.5 text-xs font-semibold ${sale.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                    {sale.status}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Financials Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Financials</h3>
                        <div className="mt-3 space-y-2">
                            <div className="flex justify-between">
                                <span className="text-sm text-muted-foreground">Subtotal</span>
                                <span className="text-sm">{formatGHS(sale.subtotal)}</span>
                            </div>
                            {sale.discount > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Discount</span>
                                    <span className="text-sm text-red-500">-{formatGHS(sale.discount)}</span>
                                </div>
                            )}
                            <div className="flex justify-between border-t pt-2">
                                <span className="text-sm font-semibold">Total</span>
                                <span className="text-sm font-bold">{formatGHS(sale.total)}</span>
                            </div>
                            {sale.amount_tendered != null && (
                                <>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Tendered</span>
                                        <span className="text-sm">{formatGHS(sale.amount_tendered)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Change</span>
                                        <span className="text-sm font-medium text-sadiq-green">{formatGHS(sale.change_given ?? 0)}</span>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>

                    {/* Notes Card */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-5">
                        <h3 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Notes</h3>
                        <p className="mt-3 text-sm text-muted-foreground">{sale.notes ?? 'No notes for this sale.'}</p>
                    </div>
                </div>

                {/* Items Table */}
                {sale.items && sale.items.length > 0 && (
                    <div className="mt-6 overflow-hidden rounded-lg border">
                        <table className="w-full">
                            <thead className="border-b bg-muted">
                                <tr>
                                    <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Product</th>
                                    <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Qty</th>
                                    <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Unit Price</th>
                                    <th className="p-4 text-left text-xs font-semibold uppercase tracking-wide">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {sale.items.map((item) => (
                                    <tr key={item.id} className="border-t transition-colors hover:bg-muted/50">
                                        <td className="p-4 text-sm font-medium">{item.product?.name ?? '—'}</td>
                                        <td className="p-4 text-sm">{item.quantity}</td>
                                        <td className="p-4 text-sm">{formatGHS(item.unit_price)}</td>
                                        <td className="p-4 text-sm font-semibold">{formatGHS(item.line_total)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
