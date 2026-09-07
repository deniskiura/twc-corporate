<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StatCard from '@/components/admin/StatCard.vue';
import SubscriptionsTable from '@/components/admin/SubscriptionsTable.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { formatMoney, resetDate } from '@/lib/format';
import { overview } from '@/routes/admin';
import { index as subscriptions } from '@/routes/admin/subscriptions';
import type { AdminStats, BillingCycle, SubscriptionRow } from '@/types';

defineProps<{
    stats: AdminStats;
    billingCycle: BillingCycle;
    recentSubscriptions: SubscriptionRow[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Overview',
                href: overview(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Overview" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Overview"
            description="Across every company on TWC Corporate"
        />

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <StatCard label="Companies" :value="stats.companies" />
            <StatCard
                label="Joined seats"
                :value="stats.joined_seats"
                :hint="`${stats.active_subscriptions} active subscriptions`"
            />
            <StatCard
                label="Pending invites"
                :value="stats.pending_invites"
                :hint="
                    stats.stale_invites > 0
                        ? `${stats.stale_invites} waiting over two weeks`
                        : 'None waiting over two weeks'
                "
                :tone="stats.stale_invites > 0 ? 'warning' : undefined"
            />
            <StatCard
                label="Monthly run-rate"
                :value="formatMoney(stats.monthly_run_rate, 'KES')"
                hint="List price of active seats, before proration"
            />
            <StatCard
                label="Credits this month"
                :value="`${stats.credits.used} / ${stats.credits.allowance}`"
                :hint="`Used of granted. Resets on ${resetDate(billingCycle.end)}`"
            />
            <StatCard
                label="Users"
                :value="stats.users"
                hint="Staff, admins, employees and members"
            />
        </div>

        <section class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h3 class="font-medium">Recent subscriptions</h3>
                <Button as-child variant="outline" size="sm">
                    <Link :href="subscriptions()">View all</Link>
                </Button>
            </div>
            <SubscriptionsTable :rows="recentSubscriptions" show-company />
        </section>
    </div>
</template>
