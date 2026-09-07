<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SubscriptionsTable from '@/components/admin/SubscriptionsTable.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { index as subscriptionsIndex } from '@/routes/admin/subscriptions';
import type { Paginated, SubscriptionRow } from '@/types';

type Filter = '' | 'active' | 'ended';

defineProps<{
    subscriptions: Paginated<SubscriptionRow>;
    status: Filter;
    counts: Record<'all' | 'active' | 'ended', number>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Subscriptions',
                href: subscriptionsIndex(),
            },
        ],
    },
});

const filters: { key: Filter; label: string }[] = [
    { key: '', label: 'All' },
    { key: 'active', label: 'Active' },
    { key: 'ended', label: 'Ended' },
];

function hrefFor(filter: Filter): string {
    return filter
        ? subscriptionsIndex.url({ query: { status: filter } })
        : subscriptionsIndex.url();
}
</script>

<template>
    <Head title="Subscriptions" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Subscriptions"
            description="Every billable seat across all companies, newest first"
        />

        <div class="flex flex-wrap gap-2">
            <Link
                v-for="filter in filters"
                :key="filter.key"
                :href="hrefFor(filter.key)"
                preserve-scroll
                class="rounded-full border px-3 py-1 text-sm transition-colors"
                :class="
                    status === filter.key
                        ? 'bg-primary text-primary-foreground border-primary'
                        : 'hover:bg-accent'
                "
            >
                {{ filter.label }}
                <span class="ml-1 opacity-70">
                    {{ counts[filter.key || 'all'] }}
                </span>
            </Link>
        </div>

        <SubscriptionsTable :rows="subscriptions.data" show-company />
        <Pagination :paginator="subscriptions" />
    </div>
</template>
