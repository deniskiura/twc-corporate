<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineProps<{
    paginator: Paginated<unknown>;
}>();
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="text-muted-foreground flex items-center justify-between text-sm"
    >
        <span>
            Showing {{ paginator.from }}&ndash;{{ paginator.to }} of
            {{ paginator.total }}
        </span>
        <div class="flex gap-2">
            <Button
                v-if="paginator.prev_page_url"
                as-child
                variant="outline"
                size="sm"
            >
                <Link :href="paginator.prev_page_url" preserve-scroll>
                    Previous
                </Link>
            </Button>
            <Button v-else variant="outline" size="sm" disabled>
                Previous
            </Button>

            <Button
                v-if="paginator.next_page_url"
                as-child
                variant="outline"
                size="sm"
            >
                <Link :href="paginator.next_page_url" preserve-scroll>
                    Next
                </Link>
            </Button>
            <Button v-else variant="outline" size="sm" disabled>Next</Button>
        </div>
    </div>
</template>
