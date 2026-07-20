import { Head, Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Clock3,
    Package,
    Phone,
    Receipt,
    ShieldCheck,
    ShoppingCart,
    Users,
    Wallet,
} from 'lucide-react';
import { dashboard, login } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props as {
        auth: { user?: { name: string } | null };
    };

    const modules = [
        {
            title: 'Point of Sale',
            description: 'Fast checkout flow with receipt-ready transactions.',
            icon: ShoppingCart,
        },
        {
            title: 'Products & Stock',
            description: 'Track parts, quantities, and low-stock alerts in one place.',
            icon: Package,
        },
        {
            title: 'Customers',
            description: 'Keep buyer records and view purchase history quickly.',
            icon: Users,
        },
        {
            title: 'Sales History',
            description: 'Review every transaction with totals, status, and details.',
            icon: Receipt,
        },
        {
            title: 'Expenses',
            description: 'Log daily business costs and monitor monthly totals.',
            icon: Wallet,
        },
        {
            title: 'Reports',
            description: 'Generate business insights for smarter decisions.',
            icon: BarChart3,
        },
    ];

    return (
        <>
            <Head title="Sabr 89" />

            <div className="relative min-h-screen overflow-hidden bg-[#F7F6F3] text-[#1A1A1A]">
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_10%_10%,rgba(200,65,10,0.08),transparent_40%),radial-gradient(circle_at_90%_20%,rgba(15,110,86,0.08),transparent_35%),radial-gradient(circle_at_50%_90%,rgba(17,17,17,0.06),transparent_40%)]" />

                <div className="relative mx-auto flex w-full max-w-6xl flex-col px-6 py-6 lg:px-10 lg:py-8">
                    <header className="mb-10 flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <img
                                src="/sabr-89-favicon.svg"
                                alt="Sabr 89"
                                className="h-10 w-10 rounded-md border border-[#E0DDD8] bg-white p-1"
                            />
                            <div>
                                <p className="font-display text-xl leading-none">Sabr</p>
                                <p className="text-xs uppercase tracking-[0.2em] text-[#6B7280]">Auto Parts</p>
                            </div>
                        </div>

                        <div className="flex items-center gap-6">
                            <div className="hidden items-center gap-2 text-sm md:flex">
                                <Phone className="h-4 w-4 text-[#C8410A]" />
                                <div className="flex flex-col">
                                    <span className="font-medium text-[#1A1A1A]">054 223 9154</span>
                                    <span className="text-[10px] text-[#6B7280]">0537202641</span>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                {auth.user ? (
                                    <Link
                                        href={dashboard()}
                                        className="inline-flex items-center rounded-md bg-[#111111] px-4 py-2 text-sm font-medium text-white hover:opacity-90"
                                    >
                                        Open Dashboard
                                    </Link>
                                ) : (
                                    <Link
                                        href={login()}
                                        className="inline-flex items-center rounded-md border border-[#E0DDD8] bg-white px-4 py-2 text-sm font-medium hover:bg-[#F1EFEB]"
                                    >
                                        Log in
                                    </Link>
                                )}
                            </div>
                        </div>
                    </header>

                    <main className="space-y-8">
                        <section className="grid gap-6 rounded-3xl border border-[#E0DDD8] bg-white p-6 shadow-sm lg:grid-cols-[1.2fr_0.8fr] lg:p-8">
                            <div>
                                <p className="mb-3 inline-flex rounded-full bg-[#FDF0E9] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#C8410A]">
                                    Retail Operations Simplified
                                </p>
                                <h1 className="font-display text-4xl leading-tight tracking-tight lg:text-5xl">
                                    Precision Control for <span className="text-[#C8410A]">Sabr 89</span>.
                                </h1>
                                <p className="mt-4 max-w-xl text-sm leading-7 text-[#6B7280] lg:text-base">
                                    Manage your spare parts inventory, track every sale, and monitor business health
                                    with a system built for speed and reliability at the counter.
                                </p>

                                <div className="mt-6 flex flex-wrap gap-3">
                                    <Link
                                        href={auth.user ? dashboard() : login()}
                                        className="inline-flex items-center rounded-md bg-[#111111] px-5 py-2.5 text-sm font-medium text-white hover:opacity-90"
                                    >
                                        {auth.user ? 'Continue to Dashboard' : 'Start with Login'}
                                    </Link>
                                </div>
                            </div>

                            <div className="grid gap-3">
                                <div className="rounded-2xl bg-[#111111] p-4 text-white">
                                    <p className="text-xs uppercase tracking-wide text-white/70">Today Focus</p>
                                    <p className="mt-2 text-2xl font-semibold">Counter Sales + Stock Health</p>
                                    <p className="mt-2 text-sm text-white/75">See live movement and act before stock runs low.</p>
                                </div>
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="rounded-2xl bg-[#C8410A] p-4 text-white">
                                        <Clock3 className="mb-2 h-5 w-5" />
                                        <p className="text-sm font-medium">Fast Checkout</p>
                                        <p className="text-xs text-white/80">Built for busy counter hours</p>
                                    </div>
                                    <div className="rounded-2xl bg-[#0F6E56] p-4 text-white">
                                        <ShieldCheck className="mb-2 h-5 w-5" />
                                        <p className="text-sm font-medium">Role Access</p>
                                        <p className="text-xs text-white/80">Secure team permissions</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-3xl border border-[#E0DDD8] bg-white p-6 shadow-sm lg:p-8">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <h2 className="font-display text-2xl">Core Modules</h2>
                                <p className="text-sm text-[#6B7280]">Everything you need to run the shop daily</p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                {modules.map((module) => {
                                    const Icon = module.icon;

                                    return (
                                        <article
                                            key={module.title}
                                            className="rounded-xl border border-[#ECE9E4] bg-[#FCFBF9] p-4 transition-colors hover:bg-[#F7F4EF]"
                                        >
                                            <Icon className="mb-3 h-5 w-5 text-[#C8410A]" />
                                            <h3 className="text-sm font-semibold">{module.title}</h3>
                                            <p className="mt-1 text-xs leading-5 text-[#6B7280]">{module.description}</p>
                                        </article>
                                    );
                                })}
                            </div>
                        </section>
                    </main>

                    <footer className="mt-8 rounded-2xl border border-[#E0DDD8] bg-white px-6 py-4 text-sm text-[#6B7280]">
                        <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <div className="flex flex-col gap-1">
                                <p className="font-medium text-[#1A1A1A]">Sabr 89 Management System</p>
                                <p>Kumasi, Ghana • 054 223 9154 / 0537202641</p>
                            </div>
                            <p>© {new Date().getFullYear()} Sabr 89</p>
                        </div>
                    </footer>
                </div>
            </div>
        </>
    );
}