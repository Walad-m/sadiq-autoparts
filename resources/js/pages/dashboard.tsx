import { Head, usePage, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { formatGHS } from '@/lib/constants';
import type { Product, Sale, PaginatedData } from '@/types';
import {
    BadgeCent,
    Receipt,
    Package,
    Users,
    TrendingUp,
    Clock,
    CreditCard,
    BarChart3,
    AlertTriangle,
} from 'lucide-react';

interface DashboardProps {
    todaySales: number;
    todayTransactions: number;
    totalProducts: number;
    totalCustomers: number;
    monthlyRevenue: number;
    monthlyExpenses: number;
    grossProfit: number;
    lowStockProducts: Pick<Product, 'id' | 'name' | 'quantity' | 'reorder_level'>[];
    recentSales: Sale[];
}

const kpiCards = [
    { key: 'todaySales', label: "Today's Sales", icon: BadgeCent, color: 'bg-sabr-red', format: true },
    { key: 'todayTransactions', label: 'Transactions', icon: Receipt, color: 'bg-sabr-gold text-gray-900', format: false },
    { key: 'totalProducts', label: 'Total Products', icon: Package, color: 'bg-sabr-charcoal', format: false },
    { key: 'totalCustomers', label: 'Customers', icon: Users, color: 'bg-sabr-teal', format: false },
    { key: 'monthlyRevenue', label: 'Monthly Revenue', icon: TrendingUp, color: 'bg-sabr-blue', format: true },
    { key: 'monthlyExpenses', label: 'Expenses (Month)', icon: CreditCard, color: 'bg-sabr-coral', format: true },
    { key: 'grossProfit', label: 'Gross Profit', icon: BarChart3, color: 'bg-sabr-green', format: true },
] as const;

export default function Dashboard(props: DashboardProps) {
    const { auth } = usePage().props as any;

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                {/* Welcome */}
                <div>
                    <h2 className="font-display text-2xl font-bold">
                        Welcome back, {auth?.user?.name ?? 'Team'}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Here&apos;s what&apos;s happening with your business today.
                    </p>
                </div>

                {/* KPI Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {kpiCards.map((card) => {
                        const Icon = card.icon;
                        const value = props[card.key as keyof DashboardProps] as number;
                        return (
                            <div
                                key={card.key}
                                className={`relative overflow-hidden rounded-xl p-5 text-white ${card.color}`}
                            >
                                <Icon className="absolute top-4 right-4 h-10 w-10 opacity-20" />
                                <p className="text-xs font-semibold uppercase tracking-wide opacity-80">
                                    {card.label}
                                </p>
                                <p className="mt-2 text-2xl font-bold">
                                    {card.format ? formatGHS(value ?? 0) : (value ?? 0)}
                                </p>
                            </div>
                        );
                    })}
                </div>

                {/* Bottom section — Recent sales + Low-stock alerts */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Recent Sales */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                        <div className="flex items-center justify-between">
                            <h3 className="font-display text-lg font-semibold">Recent Sales</h3>
                            <Link href="/sales" className="text-sm text-sabr-blue hover:underline">
                                View All
                            </Link>
                        </div>
                        {props.recentSales?.length > 0 ? (
                            <table className="mt-4 w-full">
                                <thead className="border-b">
                                    <tr>
                                        <th className="p-2 text-left text-xs font-medium text-muted-foreground">Customer</th>
                                        <th className="p-2 text-left text-xs font-medium text-muted-foreground">Amount</th>
                                        <th className="p-2 text-left text-xs font-medium text-muted-foreground">Payment</th>
                                        <th className="p-2 text-left text-xs font-medium text-muted-foreground">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {props.recentSales.map((sale) => (
                                        <tr key={sale.id} className="border-t">
                                            <td className="p-2 text-sm">{sale.customer?.name ?? 'Walk-in'}</td>
                                            <td className="p-2 text-sm font-semibold">{formatGHS(sale.total)}</td>
                                            <td className="p-2 text-sm">
                                                <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${sale.payment_method === 'momo'
                                                        ? 'bg-sabr-teal/10 text-sabr-teal'
                                                        : 'bg-sabr-green/10 text-sabr-green'
                                                    }`}>
                                                    {sale.payment_method === 'momo' ? 'MoMo' : 'Cash'}
                                                </span>
                                            </td>
                                            <td className="p-2 text-sm text-muted-foreground">
                                                {new Date(sale.created_at).toLocaleDateString()}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <p className="mt-4 text-sm text-muted-foreground">No sales yet.</p>
                        )}
                    </div>

                    {/* Low-stock Alerts */}
                    <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                        <div className="flex items-center gap-2">
                            <AlertTriangle className="h-5 w-5 text-sabr-coral" />
                            <h3 className="font-display text-lg font-semibold">Low-Stock Alerts</h3>
                        </div>
                        {props.lowStockProducts?.length > 0 ? (
                            <div className="mt-4 space-y-3">
                                {props.lowStockProducts.map((product) => {
                                    const percent = product.reorder_level > 0
                                        ? Math.min((product.quantity / product.reorder_level) * 100, 100)
                                        : 0;
                                    return (
                                        <div key={product.id}>
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="font-medium">{product.name}</span>
                                                <span className={`font-semibold ${product.quantity === 0 ? 'text-red-600' : 'text-sabr-coral'
                                                    }`}>
                                                    {product.quantity} left
                                                </span>
                                            </div>
                                            <div className="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                                <div
                                                    className={`h-full rounded-full transition-all ${product.quantity === 0
                                                            ? 'bg-red-500'
                                                            : 'bg-sabr-coral'
                                                        }`}
                                                    style={{ width: `${percent}%` }}
                                                />
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <p className="mt-4 text-sm text-muted-foreground">All stock levels are healthy.</p>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

