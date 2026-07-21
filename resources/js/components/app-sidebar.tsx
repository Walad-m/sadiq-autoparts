import { Link, usePage } from '@inertiajs/react';
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
    Shield,
    UserCog,
    ClipboardList,
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
import { usePermission } from '@/hooks/use-permission';
import type { NavGroup, NavItem } from '@/types';

export function AppSidebar() {
    const { can } = usePermission();

    /**
     * Filter a list of nav items, keeping only those whose required
     * permission (if any) the current user holds.
     */
    function gated(items: (NavItem & { permission?: string })[]): NavItem[] {
        return items.filter((item) => !item.permission || can(item.permission));
    }

    const rawGroups: (Omit<NavGroup, 'items'> & {
        permission?: string;
        items: (NavItem & { permission?: string })[];
    })[] = [
        {
            title: 'MAIN',
            items: [
                { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard, permission: 'view-dashboard' },
            ],
        },
        {
            title: 'SALES & OPERATIONS',
            items: [
                { title: 'Point of Sale', href: '/pos',     icon: Monitor,    permission: 'access-pos' },
                { title: 'Sales',         href: '/sales',   icon: ShoppingCart, permission: 'view-sales' },
                { title: 'Expenses',      href: '/expenses',icon: FileText,   permission: 'view-expenses' },
            ],
        },
        {
            title: 'INVENTORY',
            items: [
                { title: 'Products',   href: '/products',   icon: Package, permission: 'view-products' },
                { title: 'Categories', href: '/categories', icon: Tags,    permission: 'view-categories' },
            ],
        },
        {
            title: 'RELATIONSHIPS',
            items: [
                { title: 'Customers', href: '/customers', icon: Users,  permission: 'view-customers' },
                { title: 'Suppliers', href: '/suppliers', icon: Truck,  permission: 'view-suppliers' },
            ],
        },
        {
            title: 'ANALYTICS',
            items: [
                { title: 'Reports', href: '/reports', icon: BarChart3, permission: 'view-reports' },
            ],
        },
        {
            title: 'ADMINISTRATION',
            permission: 'manage-users',   // hide the whole group unless admin
            items: [
                { title: 'Users',         href: '/admin/users',         icon: UserCog,     permission: 'manage-users' },
                { title: 'Roles',         href: '/admin/roles',         icon: Shield,      permission: 'manage-roles' },
                { title: 'Activity Log',  href: '/admin/activity-log',  icon: ClipboardList, permission: 'manage-users' },
            ],
        },
    ];

    // Filter groups: hide whole group if its top-level permission is denied,
    // then filter individual items within each visible group.
    const navGroups: NavGroup[] = rawGroups
        .filter((g) => !g.permission || can(g.permission))
        .map((g) => ({ title: g.title, items: gated(g.items) }))
        .filter((g) => g.items.length > 0);

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
