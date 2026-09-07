<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CreditsBar from '@/components/company/CreditsBar.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatLongDate, resetDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { Membership } from '@/types';

defineProps<{
    membership: Membership | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <Card v-if="membership" class="max-w-xl">
            <CardHeader>
                <CardDescription>Your wellness plan</CardDescription>
                <CardTitle>
                    {{ membership.company }} sponsors your
                    {{ membership.plan.name }} plan
                </CardTitle>
                <CardDescription>
                    Member since {{ formatLongDate(membership.joined_at) }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div>
                    <p class="text-4xl font-semibold">
                        {{ membership.credits.remaining }}
                        <span
                            class="text-muted-foreground text-base font-normal"
                        >
                            credits left this month
                        </span>
                    </p>
                    <p
                        v-if="membership.credits.purchased > 0"
                        class="text-muted-foreground text-sm"
                    >
                        Plus {{ membership.credits.purchased }} you bought
                        yourself.
                    </p>
                </div>

                <CreditsBar :credits="membership.credits" />

                <p class="text-muted-foreground text-sm">
                    <template v-if="membership.credits.exhausted">
                        You've used this month's allowance. You can request more
                        from your account manager, or wait for the next plan
                        cycle reset on
                        {{ resetDate(membership.billing_cycle.end) }}.
                    </template>
                    <template v-else>
                        Unused credits expire on
                        {{ resetDate(membership.billing_cycle.end) }}, when your
                        next {{ membership.plan.monthly_credits }} arrive.
                    </template>
                </p>
            </CardContent>
        </Card>

        <Card v-else class="max-w-xl">
            <CardHeader>
                <CardTitle>Welcome</CardTitle>
                <CardDescription>
                    You're not on a company plan yet. If your employer invited
                    you, open the link they sent to join their plan.
                </CardDescription>
            </CardHeader>
        </Card>
    </div>
</template>
