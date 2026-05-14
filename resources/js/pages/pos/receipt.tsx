import { Head, Link } from '@inertiajs/react';
import { formatGHS, APP_NAME, SHOP_PHONE, SHOP_LOCATION } from '@/lib/constants';
import type { Sale } from '@/types';
import { Printer, ArrowLeft, Monitor } from 'lucide-react';
import { useRef } from 'react';
import Barcode from 'react-barcode';
import { useReactToPrint } from 'react-to-print';

interface Props {
    sale: Sale;
}

function formatDate(dateStr: string): string {
    try {
        return new Intl.DateTimeFormat('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        }).format(new Date(dateStr));
    } catch {
        return dateStr;
    }
}

function formatPaymentLabel(method: string): string {
    if (method === 'momo') return 'Mobile Money (MoMo)';
    return method.charAt(0).toUpperCase() + method.slice(1);
}

export default function PosReceipt({ sale }: Props) {
    const receiptRef = useRef<HTMLDivElement>(null);

    const handlePrint = useReactToPrint({
        contentRef: receiptRef,
        documentTitle: `Receipt-${sale.sale_number}`,
    });

    return (
        <>
            <Head title={`Receipt — ${sale.sale_number}`} />

            <div className="flex flex-col items-center gap-6 p-6">
                {/* Action Bar */}
                <div className="flex w-full max-w-2xl flex-wrap items-center justify-between gap-3 print:hidden">
                    <div>
                        <h1 className="font-display text-2xl font-bold">Sale Complete ✓</h1>
                        <p className="text-sm text-muted-foreground">Receipt is ready for printing.</p>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => handlePrint()}
                            className="inline-flex items-center gap-2 rounded-lg bg-sadiq-red px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sadiq-red/90"
                        >
                            <Printer className="h-4 w-4" />
                            Print Receipt
                        </button>
                        <Link
                            href="/pos"
                            className="inline-flex items-center gap-2 rounded-lg border border-input px-5 py-2.5 text-sm font-medium transition-colors hover:bg-muted"
                        >
                            <Monitor className="h-4 w-4" />
                            New Sale
                        </Link>
                        <Link
                            href="/sales"
                            className="inline-flex items-center gap-2 rounded-lg border border-input px-5 py-2.5 text-sm font-medium transition-colors hover:bg-muted"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Sales History
                        </Link>
                    </div>
                </div>

                {/* Printable Receipt */}
                <div
                    ref={receiptRef}
                    className="w-full max-w-[80mm] rounded-2xl border bg-white shadow-sm print:max-w-none print:rounded-none print:border-0 print:shadow-none"
                >
                    <div className="p-4 font-mono text-[12px] leading-tight text-black">
                        {/* Header */}
                        <div className="text-center space-y-0.5">
                            <p className="text-sm font-bold uppercase tracking-tight">{APP_NAME}</p>
                            <p className="text-[10px] italic">Quality Auto Parts — Best Prices</p>
                            <p>{SHOP_LOCATION}</p>
                            <p>Tel: {SHOP_PHONE}</p>
                        </div>

                        <div className="my-3 border-t border-b border-dashed py-2 text-center">
                            <p className="font-bold">SALES RECEIPT</p>
                        </div>

                        {/* Sale Info */}
                        <div className="space-y-0.5">
                            <p>Receipt: {sale.sale_number}</p>
                            <p>Date: {formatDate(sale.created_at)}</p>
                            <p>Served by: {sale.user?.name ?? 'System'}</p>
                            {sale.customer && <p>Customer: {sale.customer.name}</p>}
                        </div>

                        {/* Items */}
                        <div className="mt-3 border-t border-dashed pt-2">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="border-b border-dashed font-bold">
                                        <th className="pb-1">Item</th>
                                        <th className="pb-1 text-center">Qty</th>
                                        <th className="pb-1 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-dashed">
                                    {sale.items?.map((item) => (
                                        <tr key={item.id}>
                                            <td className="py-1.5 align-top">
                                                <div className="font-medium">{item.product?.name ?? 'Product'}</div>
                                                <div className="opacity-60">{formatGHS(item.unit_price)}</div>
                                            </td>
                                            <td className="py-1.5 text-center align-top">{item.quantity}</td>
                                            <td className="py-1.5 text-right align-top font-bold">{formatGHS(item.line_total)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals */}
                        <div className="mt-3 border-t border-dashed pt-2 space-y-0.5">
                            <div className="flex justify-between">
                                <span>Total Items:</span>
                                <span>{sale.items?.reduce((s, i) => s + i.quantity, 0) ?? 0}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{formatGHS(sale.subtotal)}</span>
                            </div>
                            {sale.discount > 0 && (
                                <div className="flex justify-between">
                                    <span>Discount:</span>
                                    <span>-{formatGHS(sale.discount)}</span>
                                </div>
                            )}
                            <div className="flex justify-between text-[14px] font-bold">
                                <span>GRAND TOTAL:</span>
                                <span>{formatGHS(sale.total)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Payment:</span>
                                <span>{formatPaymentLabel(sale.payment_method)}</span>
                            </div>
                            {sale.momo_reference && (
                                <div className="flex justify-between">
                                    <span>Ref:</span>
                                    <span>{sale.momo_reference}</span>
                                </div>
                            )}
                            {sale.amount_tendered != null && (
                                <>
                                    <div className="flex justify-between">
                                        <span>Tendered:</span>
                                        <span>{formatGHS(sale.amount_tendered)}</span>
                                    </div>
                                    <div className="flex justify-between font-bold">
                                        <span>Change:</span>
                                        <span>{formatGHS(sale.change_given ?? 0)}</span>
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Footer + Barcode */}
                        <div className="mt-5 flex flex-col items-center justify-center space-y-2 border-t border-dashed pt-3">
                            {sale.sale_number && (
                                <div className="bg-white p-1">
                                    <Barcode
                                        value={sale.sale_number}
                                        width={1.2}
                                        height={36}
                                        fontSize={9}
                                        margin={0}
                                        background="#ffffff"
                                    />
                                </div>
                            )}
                            <p className="text-[10px]">Thank you for your patronage!</p>
                            <p className="text-[9px] font-bold uppercase tracking-wider">{APP_NAME}</p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
