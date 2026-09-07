<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { formatDateWithYear, formatMoney } from '@/lib/format';
import { show as companyPage } from '@/routes/admin/companies';
import type { SubscriptionRow } from '@/types';

defineProps<{
    rows: SubscriptionRow[];
    showCompany?: boolean;
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
                    <th v-if="showCompany" class="px-4 py-3 font-medium">
                        Company
                    </th>
                    <th class="px-4 py-3 font-medium">Plan</th>
                    <th class="px-4 py-3 font-medium">Started</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td
                        :colspan="showCompany ? 5 : 4"
                        class="text-muted-foreground px-4 py-8 text-center"
                    >
                        No subscriptions yet.
                    </td>
                </tr>
                <tr v-for="row in rows" :key="row.id" class="border-t">
                    <td class="px-4 py-3">
                        <div class="font-medium">
                            {{ row.employee.name ?? row.employee.email }}
                        </div>
                        <div
                            v-if="row.employee.name"
                            class="text-muted-foreground text-xs"
                        >
                            {{ row.employee.email }}
                        </div>
                    </td>
                    <td v-if="showCompany" class="px-4 py-3">
                        <Link
                            :href="companyPage({ company: row.company.id })"
                            class="underline underline-offset-4"
                        >
                            {{ row.company.name }}
                        </Link>
                    </td>
                    <td class="px-4 py-3">
                        <div>{{ row.plan.name }}</div>
                        <div class="text-muted-foreground text-xs">
                            {{
                                formatMoney(
                                    row.plan.monthly_price,
                                    row.plan.currency,
                                )
                            }}
                            a month
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        {{ formatDateWithYear(row.started_at) }}
                    </td>
                    <td class="px-4 py-3">
                        <Badge v-if="row.is_active" variant="secondary">
                            Active
                        </Badge>
                        <template v-else>
                            <Badge variant="outline">Ended</Badge>
                            <span
                                v-if="row.ended_at"
                                class="text-muted-foreground ml-2 text-xs"
                            >
                                {{ formatDateWithYear(row.ended_at) }}
                            </span>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
