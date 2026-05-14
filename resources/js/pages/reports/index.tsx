import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { formatGHS } from '@/lib/constants';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DollarSign, TrendingDown, TrendingUp } from 'lucide-react';

interface Props {
    stats: {
        today_sales: number;
        today_expenses: number;
        today_profit: number;
    };
}

export default function ReportsIndex({ stats }: Props) {
    return (
        <>
            <Head title="Analytics & Reports" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Heading
                    title="Analytics & Reports"
                    description="View key performance metrics and financial summaries."
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Today's Sales</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatGHS(stats.today_sales)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Today's Expenses</CardTitle>
                            <TrendingDown className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatGHS(stats.today_expenses)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Today's Profit</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${stats.today_profit >= 0 ? 'text-sadiq-green' : 'text-sadiq-red'}`}>
                                {formatGHS(stats.today_profit)}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="mt-8 flex flex-col items-center justify-center rounded-xl border border-dashed py-16 text-center">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 mb-4">
                        <BarChart3Icon className="h-8 w-8 text-muted-foreground" />
                    </div>
                    <h3 className="font-display text-lg font-bold">Advanced Reporting Coming Soon</h3>
                    <p className="max-w-md text-sm text-muted-foreground mt-2">
                        We are building comprehensive Profit & Loss, Inventory Valuation, and Sales by Category reports.
                    </p>
                </div>
            </div>
        </>
    );
}

function BarChart3Icon(props: React.SVGProps<SVGSVGElement>) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M3 3v18h18" />
      <path d="M18 17V9" />
      <path d="M13 17V5" />
      <path d="M8 17v-3" />
    </svg>
  )
}
