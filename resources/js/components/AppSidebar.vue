<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Boxes,
    CircleDollarSign,
    Globe,
    Headset,
    LayoutGrid,
    Mail,
    MapPin,
    Package,
    Receipt,
    ShieldCheck,
    UserCircle,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { contact, dashboard, home } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as adminContactIndex } from '@/routes/admin/contact-messages';
import { index as adminCustomersIndex } from '@/routes/admin/customers';
import { index as adminPackagesIndex } from '@/routes/admin/packages';
import { index as adminPreAlertsIndex } from '@/routes/admin/pre-alerts';
import { index as adminRatesIndex } from '@/routes/admin/shipping-rates';
import { index as adminWarehouseIndex } from '@/routes/admin/warehouse';
import { edit as profileEdit } from '@/routes/portal/profile';
import { index as preAlertsIndex } from '@/routes/portal/pre-alerts';
import { index as packagesIndex } from '@/routes/portal/packages';
import { shippingAddress } from '@/routes/portal';
import type { NavItem } from '@/types';

const page = usePage();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));

const customerNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'My shipping address', href: shippingAddress(), icon: MapPin },
    { title: 'Pre-alerts', href: preAlertsIndex(), icon: Receipt },
    { title: 'Packages', href: packagesIndex(), icon: Package },
    { title: 'My profile', href: profileEdit(), icon: UserCircle },
    { title: 'Support', href: contact(), icon: Headset },
];

const adminNavItems: NavItem[] = [
    { title: 'Overview', href: adminDashboard(), icon: LayoutGrid },
    { title: 'Customers', href: adminCustomersIndex(), icon: Users },
    { title: 'Pre-alerts', href: adminPreAlertsIndex(), icon: Receipt },
    { title: 'Packages', href: adminPackagesIndex(), icon: Package },
    { title: 'Contact inbox', href: adminContactIndex(), icon: Mail },
    { title: 'Shipping rates', href: adminRatesIndex(), icon: CircleDollarSign },
    { title: 'Warehouse', href: adminWarehouseIndex(), icon: Boxes },
];

const navItems = computed(() =>
    isAdmin.value ? adminNavItems : customerNavItems,
);

const footerNavItems: NavItem[] = [
    { title: 'Visit website', href: home(), icon: Globe },
    {
        title: 'Help & docs',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: ShieldCheck,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isAdmin ? adminDashboard() : dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
