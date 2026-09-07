<script setup lang="ts">
import { computed } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatLongDate, resetDate } from '@/lib/format';
import type { BillingCycle, TeamTotals } from '@/types';

const props = defineProps<{
    totals: TeamTotals;
    billingCycle: BillingCycle;
}>();

const usedPercent = computed(() =>
    props.totals.credits.allowance === 0
        ? 0
        : Math.min(
              100,
              Math.round(
                  (props.totals.credits.used / props.totals.credits.allowance) *
                      100,
              ),
          ),
);

const barTone = computed(() => {
    if (props.totals.credits.exhausted) {
        return 'bg-red-500';
    }

    return usedPercent.value >= 80 ? 'bg-amber-500' : 'bg-emerald-500';
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <Card>
            <CardHeader>
                <CardDescription>Joined</CardDescription>
                <CardTitle class="text-3xl">{{ totals.joined }}</CardTitle>
            </CardHeader>
            <CardContent class="text-muted-foreground text-xs">
                Seats you are billed for this month.
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardDescription>Invited, not joined</CardDescription>
                <CardTitle class="text-3xl">{{ totals.invited }}</CardTitle>
            </CardHeader>
            <CardContent
                class="text-xs"
                :class="
                    totals.stale_invites > 0
                        ? 'text-amber-700 dark:text-amber-400'
                        : 'text-muted-foreground'
                "
            >
                <template v-if="totals.stale_invites > 0">
                    {{ totals.stale_invites }} waiting over two weeks. Resend or
                    withdraw them below.
                </template>
                <template v-else>Free until they log in.</template>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardDescription>Credits this month</CardDescription>
                <CardTitle class="text-3xl">
                    {{ totals.credits.used }}
                    <span class="text-muted-foreground text-base font-normal">
                        / {{ totals.credits.allowance }}
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="bg-muted h-1.5 w-full overflow-hidden rounded-full">
                    <div
                        class="h-full rounded-full"
                        :class="barTone"
                        :style="{ width: `${usedPercent}%` }"
                    />
                </div>
                <p class="text-muted-foreground mt-2 text-xs">
                    Resets on {{ resetDate(billingCycle.end) }}.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardDescription>Next bill</CardDescription>
                <CardTitle class="text-xl">
                    {{ formatLongDate(billingCycle.bills_on) }}
                </CardTitle>
            </CardHeader>
            <CardContent class="text-muted-foreground text-xs">
                For {{ totals.joined }} joined
                {{ totals.joined === 1 ? 'seat' : 'seats' }}. Anyone who joined
                mid-month is prorated.
            </CardContent>
        </Card>
    </div>
</template>
