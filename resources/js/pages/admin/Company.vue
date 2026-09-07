<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SubscriptionsTable from '@/components/admin/SubscriptionsTable.vue';
import TeamSummary from '@/components/company/TeamSummary.vue';
import TeamTable from '@/components/company/TeamTable.vue';
import Heading from '@/components/Heading.vue';
import { formatDateWithYear, formatMoney } from '@/lib/format';
import { index as companiesIndex } from '@/routes/admin/companies';
import type {
    BillingCycle,
    CompanySummary,
    SponsoredUser,
    SubscriptionRow,
    TeamTotals,
} from '@/types';

defineProps<{
    company: CompanySummary;
    seats: SponsoredUser[];
    totals: TeamTotals;
    billingCycle: BillingCycle;
    subscriptions: SubscriptionRow[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Companies',
                href: companiesIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="company.name" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                :title="company.name"
                :description="`On TWC Corporate since ${formatDateWithYear(company.created_at)}`"
            />
            <div class="text-right">
                <div class="text-muted-foreground text-xs">
                    Monthly run-rate
                </div>
                <div class="text-xl font-semibold">
                    {{ formatMoney(company.monthly_run_rate, 'KES') }}
                </div>
            </div>
        </div>

        <section class="rounded-xl border p-4">
            <h3 class="mb-2 text-sm font-medium">Admins</h3>
            <ul class="flex flex-wrap gap-x-6 gap-y-1 text-sm">
                <li v-for="admin in company.admins" :key="admin.id">
                    <span class="font-medium">{{ admin.name }}</span>
                    <span class="text-muted-foreground ml-2">
                        {{ admin.email }}
                    </span>
                </li>
            </ul>
        </section>

        <TeamSummary :totals="totals" :billing-cycle="billingCycle" />

        <section class="flex flex-col gap-3">
            <h3 class="font-medium">Team, as the admin sees it</h3>
            <p
                v-if="seats.length === 0"
                class="text-muted-foreground rounded-xl border border-dashed p-8 text-center text-sm"
            >
                Nobody invited yet.
            </p>
            <TeamTable v-else :seats="seats" :busy-seat-id="null" readonly />
        </section>

        <section class="flex flex-col gap-3">
            <h3 class="font-medium">Subscription history</h3>
            <SubscriptionsTable :rows="subscriptions" />
        </section>
    </div>
</template>
