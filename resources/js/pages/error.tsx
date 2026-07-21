import { Head, Link } from '@inertiajs/react';
import { Home, ArrowLeft, AlertTriangle, ShieldOff, ServerCrash, SearchX } from 'lucide-react';

interface Props {
    status: number;
}

const ERROR_CONFIG: Record<number, {
    icon: React.ElementType;
    title: string;
    description: string;
    iconColor: string;
    iconBg: string;
    accentColor: string;
}> = {
    403: {
        icon: ShieldOff,
        title: "Access Denied",
        description: "You don't have permission to view this page. If you think this is a mistake, contact your administrator.",
        iconColor: 'text-amber-500',
        iconBg: 'bg-amber-50 dark:bg-amber-950/30',
        accentColor: 'from-amber-500/10 via-transparent',
    },
    404: {
        icon: SearchX,
        title: "Page Not Found",
        description: "The page you're looking for doesn't exist or may have been moved. Double-check the URL or head back to the dashboard.",
        iconColor: 'text-blue-500',
        iconBg: 'bg-blue-50 dark:bg-blue-950/30',
        accentColor: 'from-blue-500/10 via-transparent',
    },
    419: {
        icon: AlertTriangle,
        title: "Session Expired",
        description: "Your session has expired. Please refresh the page and try again.",
        iconColor: 'text-amber-500',
        iconBg: 'bg-amber-50 dark:bg-amber-950/30',
        accentColor: 'from-amber-500/10 via-transparent',
    },
    429: {
        icon: AlertTriangle,
        title: "Too Many Requests",
        description: "You've made too many requests in a short period. Please wait a moment before trying again.",
        iconColor: 'text-orange-500',
        iconBg: 'bg-orange-50 dark:bg-orange-950/30',
        accentColor: 'from-orange-500/10 via-transparent',
    },
    500: {
        icon: ServerCrash,
        title: "Server Error",
        description: "Something went wrong on our end. Our team has been notified. Please try again in a moment.",
        iconColor: 'text-red-500',
        iconBg: 'bg-red-50 dark:bg-red-950/30',
        accentColor: 'from-red-500/10 via-transparent',
    },
    503: {
        icon: ServerCrash,
        title: "Service Unavailable",
        description: "The system is temporarily offline for maintenance. Please check back shortly.",
        iconColor: 'text-red-500',
        iconBg: 'bg-red-50 dark:bg-red-950/30',
        accentColor: 'from-red-500/10 via-transparent',
    },
};

const FALLBACK = {
    icon: AlertTriangle,
    title: "Something Went Wrong",
    description: "An unexpected error occurred. Please try again or contact support if the problem persists.",
    iconColor: 'text-gray-500',
    iconBg: 'bg-gray-50 dark:bg-gray-950/30',
    accentColor: 'from-gray-500/10 via-transparent',
};

export default function ErrorPage({ status }: Props) {
    const config = ERROR_CONFIG[status] ?? FALLBACK;
    const Icon = config.icon;

    return (
        <>
            <Head title={`${status} — ${config.title}`} />

            {/* Full-page layout — no app shell */}
            <div className="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background px-4">

                {/* Subtle gradient blob */}
                <div
                    className={`pointer-events-none absolute inset-0 bg-gradient-to-br ${config.accentColor} to-transparent`}
                />

                {/* Floating decorative grid */}
                <div
                    className="pointer-events-none absolute inset-0 opacity-[0.03]"
                    style={{
                        backgroundImage: 'linear-gradient(currentColor 1px, transparent 1px), linear-gradient(90deg, currentColor 1px, transparent 1px)',
                        backgroundSize: '40px 40px',
                    }}
                />

                <div className="relative z-10 flex max-w-md flex-col items-center text-center">
                    {/* Status code */}
                    <p className="mb-4 text-7xl font-black tracking-tighter text-foreground/10 select-none">
                        {status}
                    </p>

                    {/* Icon */}
                    <div className={`mb-6 flex h-20 w-20 items-center justify-center rounded-2xl ${config.iconBg} ring-1 ring-inset ring-foreground/5`}>
                        <Icon className={`h-10 w-10 ${config.iconColor}`} strokeWidth={1.5} />
                    </div>

                    {/* Text */}
                    <h1 className="mb-3 text-2xl font-bold text-foreground">
                        {config.title}
                    </h1>
                    <p className="mb-8 text-sm leading-relaxed text-muted-foreground">
                        {config.description}
                    </p>

                    {/* Actions */}
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-2 rounded-lg bg-sabr-red px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-sabr-red/90"
                        >
                            <Home className="h-4 w-4" />
                            Go to Dashboard
                        </Link>
                        <button
                            onClick={() => window.history.back()}
                            className="inline-flex items-center gap-2 rounded-lg border border-input px-5 py-2.5 text-sm font-medium transition-colors hover:bg-muted"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Go Back
                        </button>
                    </div>

                    {/* Footer hint */}
                    <p className="mt-10 text-xs text-muted-foreground/60">
                        Error code {status} · Sabr 89 Auto Parts
                    </p>
                </div>
            </div>
        </>
    );
}
