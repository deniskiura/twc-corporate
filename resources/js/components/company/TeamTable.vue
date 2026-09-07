<script setup lang="ts">
import { RefreshCw, UserX } from '@lucide/vue';
import CreditsBar from '@/components/company/CreditsBar.vue';
import SeatStatusBadge from '@/components/company/SeatStatusBadge.vue';
import { Button } from '@/components/ui/button';
import type { SponsoredUser } from '@/types';

defineProps<{
    seats: SponsoredUser[];
    busySeatId: number | null;
    readonly?: boolean;
}>();

defineEmits<{
    resend: [seat: SponsoredUser];
    revoke: [seat: SponsoredUser];
}>();
</script>

<template>
    <div class="overflow-x-auto rounded-xl border">
        <table class="w-full text-sm">
            <thead
                class="bg-muted/50 text-muted-foreground text-left text-xs tracking-wide uppercase"
            >
                <tr>
                    <th class="px-4 py-3 font-medium">Employee</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Plan</th>
                    <th class="px-4 py-3 font-medium">Credits this month</th>
                    <th
                        v-if="!readonly"
                        class="px-4 py-3 text-right font-medium"
                    >
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="seat in seats"
                    :key="seat.id"
                    class="border-t align-top"
                    :class="{
                        'bg-amber-50/60 dark:bg-amber-950/20': seat.is_stale,
                    }"
                >
                    <td class="px-4 py-3">
                        <div class="font-medium">
                            {{ seat.name ?? seat.email }}
                        </div>
                        <div
                            v-if="seat.name"
                            class="text-muted-foreground text-xs"
                        >
                            {{ seat.email }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <SeatStatusBadge :seat="seat" />
                    </td>
                    <td class="px-4 py-3">
                        <div>{{ seat.plan.name }}</div>
                        <div class="text-muted-foreground text-xs">
                            {{ seat.plan.monthly_credits }} credits a month
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <CreditsBar
                            v-if="seat.status === 'joined'"
                            :credits="seat.credits"
                        />
                        <span v-else class="text-muted-foreground text-xs">
                            {{ seat.credits.allowance }} credits once they join
                        </span>
                    </td>
                    <td v-if="!readonly" class="px-4 py-3 text-right">
                        <div
                            v-if="seat.status === 'invited'"
                            class="flex justify-end gap-1"
                        >
                            <Button
                                size="sm"
                                variant="outline"
                                :disabled="busySeatId === seat.id"
                                @click="$emit('resend', seat)"
                            >
                                <RefreshCw />
                                Resend
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                class="text-destructive hover:text-destructive"
                                :disabled="busySeatId === seat.id"
                                @click="$emit('revoke', seat)"
                            >
                                <UserX />
                                Withdraw
                            </Button>
                        </div>
                        <span v-else class="text-muted-foreground text-xs">
                            &mdash;
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
