<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { daysAgo, formatDate } from '@/lib/format';
import type { SponsoredUser } from '@/types';

defineProps<{
    seat: SponsoredUser;
}>();
</script>

<template>
    <div class="flex flex-col gap-1">
        <template v-if="seat.status === 'joined'">
            <Badge variant="secondary">Joined</Badge>
            <span v-if="seat.joined_at" class="text-muted-foreground text-xs">
                since {{ formatDate(seat.joined_at) }}
            </span>
        </template>

        <template v-else-if="seat.is_stale">
            <Badge
                variant="outline"
                class="border-amber-300 bg-amber-100 text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200"
            >
                Pending {{ seat.days_pending }} days
            </Badge>
            <span class="text-muted-foreground text-xs">
                sent {{ formatDate(seat.last_sent_at) }}, never opened
            </span>
        </template>

        <template v-else>
            <Badge variant="outline">Invited</Badge>
            <span class="text-muted-foreground text-xs">
                sent {{ daysAgo(seat.days_pending ?? 0) }}
            </span>
        </template>
    </div>
</template>
