<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import CreateCompanyDialog from '@/components/admin/CreateCompanyDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { formatDateWithYear, formatMoney } from '@/lib/format';
import {
    index as companiesIndex,
    show as showCompany,
} from '@/routes/admin/companies';
import type { CompanySummary } from '@/types';

defineProps<{
    companies: CompanySummary[];
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

const createOpen = ref(false);
</script>

<template>
    <Head title="Companies" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Companies"
                description="Every company on TWC Corporate and who runs it"
            />
            <Button @click="createOpen = true">
                <Plus />
                Add company
            </Button>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
                <thead
                    class="bg-muted/50 text-muted-foreground text-left text-xs tracking-wide uppercase"
                >
                    <tr>
                        <th class="px-4 py-3 font-medium">Company</th>
                        <th class="px-4 py-3 font-medium">Admins</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium">Invited</th>
                        <th class="px-4 py-3 font-medium">Monthly run-rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="companies.length === 0">
                        <td
                            colspan="5"
                            class="text-muted-foreground px-4 py-8 text-center"
                        >
                            No companies yet.
                        </td>
                    </tr>
                    <tr
                        v-for="company in companies"
                        :key="company.id"
                        class="border-t align-top"
                    >
                        <td class="px-4 py-3">
                            <Link
                                :href="showCompany({ company: company.id })"
                                class="font-medium underline underline-offset-4"
                            >
                                {{ company.name }}
                            </Link>
                            <div class="text-muted-foreground text-xs">
                                since
                                {{ formatDateWithYear(company.created_at) }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-for="admin in company.admins"
                                :key="admin.id"
                            >
                                {{ admin.name }}
                                <span class="text-muted-foreground text-xs">
                                    {{ admin.email }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ company.joined_count }}</td>
                        <td class="px-4 py-3">
                            {{ company.invited_count }}
                            <span
                                v-if="company.stale_invites_count > 0"
                                class="text-xs text-amber-700 dark:text-amber-400"
                            >
                                ({{ company.stale_invites_count }} stale)
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatMoney(company.monthly_run_rate, 'KES') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <CreateCompanyDialog v-model:open="createOpen" />
</template>
