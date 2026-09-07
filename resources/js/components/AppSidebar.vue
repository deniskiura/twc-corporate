<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, CreditCard, Gauge, LayoutGrid, Users } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes';
import { overview } from '@/routes/admin';
import { index as companies } from '@/routes/admin/companies';
import { index as subscriptions } from '@/routes/admin/subscriptions';
import { index as allUsers } from '@/routes/admin/users';
import { users as team } from '@/routes/company';
import type { NavItem } from '@/types';

const page = usePage();

// Each role has its own home: staff get the console, company admins the
// team screen, everyone else a personal dashboard.
const mainNavItems = computed<NavItem[]>(() => {
    switch (page.props.auth.user.role) {
        case 'staff':
            return [
                { title: 'Overview', href: overview(), icon: Gauge },
                { title: 'Companies', href: companies(), icon: Building2 },
                {
                    title: 'Subscriptions',
                    href: subscriptions(),
                    icon: CreditCard,
                },
                { title: 'Users', href: allUsers(), icon: Users },
            ];
        case 'company_admin':
            return [{ title: 'Team', href: team(), icon: Users }];
        default:
            return [
                { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            ];
    }
});

const homeHref = computed(() => mainNavItems.value[0].href);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="homeHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
