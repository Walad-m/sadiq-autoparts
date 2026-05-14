import { Link } from '@inertiajs/react';
import {
    LayoutDashboard,
    ShoppingCart,
    Package,
    Users,
    Truck,
    FileText,
    BarChart3,
    Monitor,
    Tags,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavGroup } from '@/types';

const navGroups: NavGroup[] = [
    {
        title: 'MAIN',
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
        ],
    },
    {
        title: 'SALES & OPERATIONS',
        items: [
            { title: 'Point of Sale', href: '/pos', icon: Monitor },
            { title: 'Sales', href: '/sales', icon: ShoppingCart },
            { title: 'Expenses', href: '/expenses', icon: FileText },
        ],
    },
    {
        title: 'INVENTORY',
        items: [
            { title: 'Products', href: '/products', icon: Package },
            { title: 'Categories', href: '/categories', icon: Tags },
        ],
    },
    {
        title: 'RELATIONSHIPS',
        items: [
            { title: 'Customers', href: '/customers', icon: Users },
            { title: 'Suppliers', href: '/suppliers', icon: Truck },
        ],
    },
    {
        title: 'ANALYTICS',
        items: [
            { title: 'Reports', href: '/reports', icon: BarChart3 },
        ],
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={navGroups} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
