<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { CreditSummary } from '@/types';

const props = defineProps<{
    credits: CreditSummary;
}>();

const percent = computed(() =>
    props.credits.allowance === 0
        ? 0
        : Math.min(
              100,
              Math.round((props.credits.used / props.credits.allowance) * 100),
          ),
);

const tone = computed(() => {
    if (props.credits.exhausted) {
        return 'bg-red-500';
    }

    return percent.value >= 80 ? 'bg-amber-500' : 'bg-emerald-500';
});
</script>

<template>
    <div class="flex min-w-40 flex-col gap-1.5">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span>
                <span class="font-medium">{{ credits.used }}</span>
                of {{ credits.allowance }} used
            </span>
            <Badge v-if="credits.exhausted" variant="destructive">
                Used up
            </Badge>
            <span v-else class="text-muted-foreground">
                {{ credits.remaining }} left
            </span>
        </div>
        <div
            class="bg-muted h-1.5 w-full overflow-hidden rounded-full"
            role="progressbar"
            :aria-valuenow="percent"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full rounded-full transition-all"
                :class="tone"
                :style="{ width: `${percent}%` }"
            />
        </div>
    </div>
</template>
