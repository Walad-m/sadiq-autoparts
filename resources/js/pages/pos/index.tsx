import { Head, router } from '@inertiajs/react';
import { formatGHS, PAYMENT_METHODS } from '@/lib/constants';
import type { Product, Customer } from '@/types';
import {
    Search, Plus, Minus, X, ShoppingCart, DollarSign,
    CheckCircle, Receipt, Trash2, User, StickyNote,
    CreditCard, Smartphone,
} from 'lucide-react';
import { useState, useRef, useEffect, useMemo } from 'react';

interface CartItem {
    product_id: number;
    name: string;
    part_number: string | null;
    unit_price: number;
    quantity: number;
    max_stock: number;
    unit: string;
}

interface Props {
    products: Product[];
    customers: Customer[];
    todayRevenue: number;
    todaySalesCount: number;
}

export default function PosIndex({ products, customers, todayRevenue, todaySalesCount }: Props) {
    const [search, setSearch] = useState('');
    const [cart, setCart] = useState<CartItem[]>([]);
    const [customerId, setCustomerId] = useState('');
    const [notes, setNotes] = useState('');
    const [paymentMethod, setPaymentMethod] = useState('cash');
    const [momoRef, setMomoRef] = useState('');
    const [amountTendered, setAmountTendered] = useState('');
    const [discount, setDiscount] = useState('');
    const [processing, setProcessing] = useState(false);
    const searchRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        searchRef.current?.focus();
    }, []);

    // Filter products based on search
    const filteredProducts = useMemo(() => {
        if (!search.trim()) return [];
        return products.filter((p) =>
            p.name.toLowerCase().includes(search.toLowerCase()) ||
            (p.part_number && p.part_number.toLowerCase().includes(search.toLowerCase())),
        ).slice(0, 8);
    }, [search, products]);

    // Cart calculations
    const subtotal = cart.reduce((sum, item) => sum + item.unit_price * item.quantity, 0);
    const discountAmount = parseFloat(discount) || 0;
    const total = Math.max(0, subtotal - discountAmount);
    const tendered = parseFloat(amountTendered) || 0;
    const change = paymentMethod === 'cash' ? Math.max(0, tendered - total) : 0;
    const canCheckout = cart.length > 0 && !processing && (paymentMethod !== 'cash' || tendered >= total);

    function addToCart(product: Product) {
        setCart((prev) => {
            const existing = prev.find((i) => i.product_id === product.id);
            if (existing) {
                if (existing.quantity >= product.quantity) return prev;
                return prev.map((i) =>
                    i.product_id === product.id ? { ...i, quantity: i.quantity + 1 } : i,
                );
            }
            return [...prev, {
                product_id: product.id,
                name: product.name,
                part_number: product.part_number,
                unit_price: product.selling_price,
                quantity: 1,
                max_stock: product.quantity,
                unit: product.unit,
            }];
        });
        setSearch('');
        searchRef.current?.focus();
    }

    function updateQty(productId: number, delta: number) {
        setCart((prev) =>
            prev.map((i) => {
                if (i.product_id !== productId) return i;
                const newQty = i.quantity + delta;
                if (newQty < 1 || newQty > i.max_stock) return i;
                return { ...i, quantity: newQty };
            }),
        );
    }

    function setQty(productId: number, quantity: number) {
        setCart((prev) =>
            prev.map((i) => {
                if (i.product_id !== productId) return i;
                const nextQty = Math.max(1, Math.min(quantity, i.max_stock));
                return { ...i, quantity: nextQty };
            }),
        );
    }

    function removeFromCart(productId: number) {
        setCart((prev) => prev.filter((i) => i.product_id !== productId));
    }

    function clearCart() {
        setCart([]);
        setDiscount('');
        setAmountTendered('');
        setMomoRef('');
        setNotes('');
        setCustomerId('');
        searchRef.current?.focus();
    }

    function handleCheckout() {
        if (!canCheckout) return;
        setProcessing(true);

        router.post('/pos', {
            customer_id: customerId || null,
            payment_method: paymentMethod,
            momo_reference: paymentMethod === 'momo' ? momoRef : null,
            discount: discountAmount,
            amount_tendered: paymentMethod === 'cash' ? tendered : null,
            change_given: paymentMethod === 'cash' ? change : null,
            notes: notes || null,
            items: cart.map((i) => ({
                product_id: i.product_id,
                quantity: i.quantity,
                unit_price: i.unit_price,
            })),
        }, {
            onError: () => setProcessing(false),
        });
    }

    return (
        <>
            <Head title="Point of Sale" />

            <div className="flex h-[calc(100vh-4rem)] flex-col overflow-hidden">
                {/* ─── TOP STATS BAR ─── */}
                <div className="border-b bg-card px-6 py-3">
                    <div className="flex items-center gap-6">
                        <h1 className="font-display text-lg font-bold">Point of Sale</h1>
                        <div className="ml-auto flex items-center gap-4">
                            <div className="flex items-center gap-2 rounded-lg border border-sidebar-border/50 bg-background px-4 py-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-sabr-green/10">
                                    <DollarSign className="h-4 w-4 text-sabr-green" />
                                </div>
                                <div>
                                    <div className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">Today's Revenue</div>
                                    <div className="text-sm font-bold">{formatGHS(todayRevenue)}</div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2 rounded-lg border border-sidebar-border/50 bg-background px-4 py-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-sabr-teal/10">
                                    <CheckCircle className="h-4 w-4 text-sabr-teal" />
                                </div>
                                <div>
                                    <div className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">Sales Today</div>
                                    <div className="text-sm font-bold">{todaySalesCount}</div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2 rounded-lg border border-sidebar-border/50 bg-background px-4 py-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-sabr-gold/10">
                                    <Receipt className="h-4 w-4 text-sabr-gold" />
                                </div>
                                <div>
                                    <div className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">Cart Items</div>
                                    <div className="text-sm font-bold">{cart.reduce((s, i) => s + i.quantity, 0)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ─── MAIN CONTENT ─── */}
                <div className="flex flex-1 overflow-hidden">
                    {/* ═══ LEFT — Search + Cart ═══ */}
                    <div className="flex flex-1 flex-col overflow-hidden">
                        {/* Search Bar */}
                        <div className="p-4 pb-0">
                            <div className="relative">
                                <div className="flex items-center gap-3 rounded-xl border-2 border-input bg-background px-4 py-3 transition-colors focus-within:border-sabr-red/50">
                                    <Search className="h-5 w-5 text-muted-foreground" />
                                    <input
                                        ref={searchRef}
                                        type="text"
                                        placeholder="Search products by name or part number..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="flex-1 bg-transparent text-base outline-none placeholder:text-muted-foreground"
                                    />
                                    {search && (
                                        <button onClick={() => setSearch('')} className="text-muted-foreground hover:text-foreground">
                                            <X className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>

                                {/* Search Results Dropdown */}
                                {filteredProducts.length > 0 && (
                                    <div className="absolute left-0 right-0 z-20 mt-1 max-h-72 overflow-y-auto rounded-xl border bg-card shadow-lg">
                                        {filteredProducts.map((p) => (
                                            <button
                                                key={p.id}
                                                onClick={() => addToCart(p)}
                                                disabled={p.quantity === 0}
                                                className="flex w-full items-center gap-3 border-b border-input/50 px-4 py-3 text-left transition-colors last:border-0 hover:bg-muted/50 disabled:cursor-not-allowed disabled:opacity-40"
                                            >
                                                <div className="flex-1 min-w-0">
                                                    <div className="truncate text-sm font-medium">{p.name}</div>
                                                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                        {p.part_number && <span>{p.part_number}</span>}
                                                        <span>·</span>
                                                        <span>{p.category?.name}</span>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-bold text-sabr-red">{formatGHS(p.selling_price)}</div>
                                                    <div className={`text-xs ${p.quantity <= 5 ? 'font-semibold text-amber-600' : 'text-muted-foreground'}`}>
                                                        {p.quantity} in stock
                                                    </div>
                                                </div>
                                                <Plus className="h-4 w-4 text-muted-foreground" />
                                            </button>
                                        ))}
                                    </div>
                                )}

                                {search.trim() && filteredProducts.length === 0 && (
                                    <div className="absolute left-0 right-0 z-20 mt-1 rounded-xl border bg-card p-6 text-center shadow-lg">
                                        <ShoppingCart className="mx-auto mb-2 h-6 w-6 text-muted-foreground" />
                                        <p className="text-sm text-muted-foreground">No products found for "{search}"</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Cart Table */}
                        <div className="flex-1 overflow-y-auto p-4">
                            {cart.length > 0 ? (
                                <div className="rounded-xl border">
                                    <div className="flex items-center justify-between border-b bg-muted/50 px-4 py-2">
                                        <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                            Cart — {cart.length} item{cart.length !== 1 ? 's' : ''}
                                        </span>
                                        <button onClick={clearCart} className="text-xs font-medium text-red-500 hover:text-red-700">
                                            Clear All
                                        </button>
                                    </div>
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                                <th className="px-4 py-2">Product</th>
                                                <th className="px-4 py-2 text-center">Qty</th>
                                                <th className="px-4 py-2 text-right">Unit Price</th>
                                                <th className="px-4 py-2 text-right">Subtotal</th>
                                                <th className="px-4 py-2 w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {cart.map((item) => (
                                                <tr key={item.product_id} className="border-b last:border-0 transition-colors hover:bg-muted/30">
                                                    <td className="px-4 py-3">
                                                        <div className="text-sm font-medium">{item.name}</div>
                                                        {item.part_number && <div className="text-xs text-muted-foreground">{item.part_number}</div>}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <div className="flex items-center justify-center gap-1">
                                                            <button
                                                                onClick={() => updateQty(item.product_id, -1)}
                                                                className="flex h-7 w-7 items-center justify-center rounded-lg border border-input transition-colors hover:bg-muted"
                                                            >
                                                                <Minus className="h-3 w-3" />
                                                            </button>
                                                            <input
                                                                type="number"
                                                                min="1"
                                                                max={item.max_stock}
                                                                value={item.quantity}
                                                                onChange={(e) => {
                                                                    const nextQty = Number(e.target.value);

                                                                    if (Number.isNaN(nextQty)) {
                                                                        return;
                                                                    }

                                                                    setQty(item.product_id, nextQty);
                                                                }}
                                                                className="h-7 w-14 rounded-lg border border-input bg-background text-center text-sm font-bold outline-none transition-colors focus:border-sabr-red/50"
                                                            />
                                                            <button
                                                                onClick={() => updateQty(item.product_id, 1)}
                                                                className="flex h-7 w-7 items-center justify-center rounded-lg border border-input transition-colors hover:bg-muted"
                                                            >
                                                                <Plus className="h-3 w-3" />
                                                            </button>
                                                        </div>
                                                        <div className="mt-1 text-center text-[10px] text-muted-foreground">{item.max_stock} avail.</div>
                                                    </td>
                                                    <td className="px-4 py-3 text-right text-sm">{formatGHS(item.unit_price)}</td>
                                                    <td className="px-4 py-3 text-right text-sm font-bold">{formatGHS(item.unit_price * item.quantity)}</td>
                                                    <td className="px-4 py-3">
                                                        <button onClick={() => removeFromCart(item.product_id)} className="rounded p-1 text-muted-foreground transition-colors hover:text-red-500">
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="flex h-full flex-col items-center justify-center text-muted-foreground">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                                        <ShoppingCart className="h-8 w-8" />
                                    </div>
                                    <p className="mt-3 text-sm font-medium">Cart is empty</p>
                                    <p className="text-xs">Search for a product above to start a sale</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* ═══ RIGHT — Customer, Payment, Checkout ═══ */}
                    <div className="flex w-[340px] flex-col border-l bg-card lg:w-[380px]">
                        <div className="flex-1 overflow-y-auto p-4 space-y-5">
                            {/* Step 2: Customer */}
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Customer</span>
                                </div>
                                <select value={customerId} onChange={(e) => setCustomerId(e.target.value)} className="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm">
                                    <option value="">Walk-in customer</option>
                                    {customers.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}{c.phone ? ` (${c.phone})` : ''}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Notes */}
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <StickyNote className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Notes</span>
                                </div>
                                <textarea
                                    value={notes}
                                    onChange={(e) => setNotes(e.target.value)}
                                    rows={2}
                                    className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm resize-none"
                                    placeholder="Add order notes..."
                                />
                            </div>

                            {/* Step 3: Payment */}
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <CreditCard className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Payment Method</span>
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <button
                                        onClick={() => setPaymentMethod('cash')}
                                        className={`flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-3 text-sm font-semibold transition-all ${
                                            paymentMethod === 'cash'
                                                ? 'border-sabr-green bg-sabr-green/10 text-sabr-green shadow-sm'
                                                : 'border-input text-muted-foreground hover:border-sabr-green/30 hover:bg-muted'
                                        }`}
                                    >
                                        <DollarSign className="h-4 w-4" />
                                        Cash
                                    </button>
                                    <button
                                        onClick={() => setPaymentMethod('momo')}
                                        className={`flex items-center justify-center gap-2 rounded-xl border-2 px-3 py-3 text-sm font-semibold transition-all ${
                                            paymentMethod === 'momo'
                                                ? 'border-sabr-teal bg-sabr-teal/10 text-sabr-teal shadow-sm'
                                                : 'border-input text-muted-foreground hover:border-sabr-teal/30 hover:bg-muted'
                                        }`}
                                    >
                                        <Smartphone className="h-4 w-4" />
                                        MoMo
                                    </button>
                                </div>
                            </div>

                            {/* MoMo Reference */}
                            {paymentMethod === 'momo' && (
                                <div className="animate-in fade-in slide-in-from-top-2">
                                    <label className="block text-xs font-medium text-muted-foreground uppercase mb-1">MoMo Transaction ID</label>
                                    <input value={momoRef} onChange={(e) => setMomoRef(e.target.value)} className="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm" placeholder="e.g. MP240512..." />
                                </div>
                            )}

                            {/* Discount */}
                            <div>
                                <label className="block text-xs font-medium text-muted-foreground uppercase mb-1">Discount (GHS)</label>
                                <input type="number" step="0.01" min="0" value={discount} onChange={(e) => setDiscount(e.target.value)} className="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm" placeholder="0.00" />
                            </div>

                            {/* Amount Tendered (cash only) */}
                            {paymentMethod === 'cash' && (
                                <div className="animate-in fade-in slide-in-from-top-2">
                                    <label className="block text-xs font-medium text-muted-foreground uppercase mb-1">Amount Tendered (GHS)</label>
                                    <input type="number" step="0.01" min="0" value={amountTendered} onChange={(e) => setAmountTendered(e.target.value)} className="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm font-semibold" placeholder="0.00" />
                                </div>
                            )}
                        </div>

                        {/* ─── TOTALS + ACTION ─── */}
                        <div className="border-t bg-background p-4 space-y-3">
                            <div className="space-y-1">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Subtotal</span>
                                    <span>{formatGHS(subtotal)}</span>
                                </div>
                                {discountAmount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Discount</span>
                                        <span className="text-red-500">-{formatGHS(discountAmount)}</span>
                                    </div>
                                )}
                            </div>

                            <div className="flex justify-between rounded-lg bg-sabr-red/5 px-3 py-2">
                                <span className="text-base font-bold">Total</span>
                                <span className="text-lg font-bold text-sabr-red">{formatGHS(total)}</span>
                            </div>

                            {paymentMethod === 'cash' && tendered > 0 && tendered >= total && (
                                <div className="flex justify-between rounded-lg bg-sabr-green/5 px-3 py-2 text-sm">
                                    <span className="font-medium text-sabr-green">Change Due</span>
                                    <span className="font-bold text-sabr-green">{formatGHS(change)}</span>
                                </div>
                            )}

                            <button
                                onClick={handleCheckout}
                                disabled={!canCheckout}
                                className="flex w-full items-center justify-center gap-2 rounded-xl bg-sabr-red py-3.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-sabr-red/90 hover:shadow-md active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                            >
                                <CheckCircle className="h-4 w-4" />
                                {processing ? 'Processing...' : `Checkout & Pay — ${formatGHS(total)}`}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

