<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateWithYear, formatRole } from '@/lib/format';
import { show as showCompany } from '@/routes/admin/companies';
import { index as usersIndex } from '@/routes/admin/users';
import type { Paginated, UserRole, UserRow } from '@/types';

const props = defineProps<{
    users: Paginated<UserRow>;
    search: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: usersIndex(),
            },
        ],
    },
});

const query = ref(props.search);

const roleVariants: Record<UserRole, 'default' | 'secondary' | 'outline'> = {
    staff: 'default',
    company_admin: 'secondary',
    employee: 'outline',
    member: 'outline',
};

function submit() {
    router.get(usersIndex.url(), query.value ? { q: query.value } : {}, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Users"
            description="Everyone with an account, across all companies"
        />

        <form class="flex gap-2" @submit.prevent="submit">
            <Input
                v-model="query"
                type="search"
                placeholder="Search by name or email"
                class="max-w-sm"
            />
            <Button type="submit" variant="outline">
                <Search />
                Search
            </Button>
        </form>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
                <thead
                    class="bg-muted/50 text-muted-foreground text-left text-xs tracking-wide uppercase"
                >
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Company</th>
                        <th class="px-4 py-3 font-medium">Since</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="users.data.length === 0">
                        <td
                            colspan="5"
                            class="text-muted-foreground px-4 py-8 text-center"
                        >
                            No users match.
                        </td>
                    </tr>
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <Badge :variant="roleVariants[user.role]">
                                {{ formatRole(user.role) }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <Link
                                v-if="user.company"
                                :href="
                                    showCompany({ company: user.company.id })
                                "
                                class="underline underline-offset-4"
                            >
                                {{ user.company.name }}
                            </Link>
                            <span v-else class="text-muted-foreground">
                                &mdash;
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDateWithYear(user.created_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="users" />
    </div>
</template>
