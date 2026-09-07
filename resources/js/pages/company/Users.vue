<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { TriangleAlert, UserPlus } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import AlertError from '@/components/AlertError.vue';
import EmptyTeam from '@/components/company/EmptyTeam.vue';
import InviteEmployeeDialog from '@/components/company/InviteEmployeeDialog.vue';
import InviteLinkNotice from '@/components/company/InviteLinkNotice.vue';
import TeamSummary from '@/components/company/TeamSummary.vue';
import TeamTable from '@/components/company/TeamTable.vue';
import Heading from '@/components/Heading.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { ApiError } from '@/lib/api';
import { resetDate } from '@/lib/format';
import { useTeamApi } from '@/lib/teamApi';
import { users } from '@/routes/company';
import type { Invitation, Plan, SponsoredUser, TeamResponse } from '@/types';

const props = defineProps<{
    company: { id: number; name: string };
    plans: Plan[];
    apiToken: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Team',
                href: users(),
            },
        ],
    },
});

const api = useTeamApi(props.apiToken);

const team = ref<TeamResponse | null>(null);
const loading = ref(true);
const loadError = ref<string | null>(null);
const inviteOpen = ref(false);
const lastInvite = ref<Invitation | null>(null);
const busySeatId = ref<number | null>(null);

const seats = computed(() => team.value?.data ?? []);

const usedPercent = computed(() => {
    const credits = team.value?.meta.totals.credits;

    if (!credits || credits.allowance === 0) {
        return 0;
    }

    return Math.round((credits.used / credits.allowance) * 100);
});

async function load() {
    loading.value = true;
    loadError.value = null;

    try {
        team.value = await api.list();
    } catch (error) {
        loadError.value =
            error instanceof ApiError
                ? error.message
                : 'Could not reach the server.';
    } finally {
        loading.value = false;
    }
}

function onInvited(invitation: Invitation) {
    lastInvite.value = invitation;
    toast.success(`Invite created for ${invitation.email}.`);
    void load();
}

async function resend(seat: SponsoredUser) {
    busySeatId.value = seat.id;

    try {
        const { data } = await api.resend(seat);
        lastInvite.value = data;
        toast.success(`New invite link ready for ${seat.email}.`);
        await load();
    } catch (error) {
        toast.error(
            error instanceof ApiError
                ? error.message
                : 'Could not resend the invite.',
        );
    } finally {
        busySeatId.value = null;
    }
}

async function revoke(seat: SponsoredUser) {
    const confirmed = window.confirm(
        `Withdraw the invite for ${seat.email}? Their link will stop working.`,
    );

    if (!confirmed) {
        return;
    }

    busySeatId.value = seat.id;

    try {
        await api.revoke(seat);

        if (lastInvite.value?.id === seat.id) {
            lastInvite.value = null;
        }

        toast.success(`Invite for ${seat.email} withdrawn.`);
        await load();
    } catch (error) {
        toast.error(
            error instanceof ApiError
                ? error.message
                : 'Could not withdraw the invite.',
        );
    } finally {
        busySeatId.value = null;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Team" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Team"
                :description="`Employees on ${company.name}'s wellness plan`"
            />
            <Button @click="inviteOpen = true">
                <UserPlus />
                Invite employee
            </Button>
        </div>

        <template v-if="loading">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Skeleton v-for="n in 4" :key="n" class="h-28" />
            </div>
            <Skeleton class="h-64" />
        </template>

        <div v-else-if="loadError" class="space-y-4">
            <AlertError title="Couldn't load your team" :errors="[loadError]" />
            <Button variant="outline" @click="load">Try again</Button>
        </div>

        <template v-else-if="team">
            <InviteLinkNotice
                v-if="lastInvite"
                :invitation="lastInvite"
                @dismiss="lastInvite = null"
            />

            <Alert
                v-if="team.meta.totals.credits.exhausted"
                class="border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"
            >
                <TriangleAlert />
                <AlertTitle>
                    Your team has used all of this month's credits
                </AlertTitle>
                <AlertDescription class="text-inherit">
                    Allowances reset on
                    {{ resetDate(team.meta.billing_cycle.end) }}. Until then,
                    employees can top up themselves in the app, or talk to us
                    about a bigger plan.
                </AlertDescription>
            </Alert>

            <Alert
                v-else-if="usedPercent >= 80"
                class="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200"
            >
                <TriangleAlert />
                <AlertTitle>
                    {{ usedPercent }}% of this month's credits are used
                </AlertTitle>
                <AlertDescription class="text-inherit">
                    Some employees may run out before allowances reset on
                    {{ resetDate(team.meta.billing_cycle.end) }}.
                </AlertDescription>
            </Alert>

            <EmptyTeam v-if="seats.length === 0" @invite="inviteOpen = true" />

            <template v-else>
                <TeamSummary
                    :totals="team.meta.totals"
                    :billing-cycle="team.meta.billing_cycle"
                />
                <TeamTable
                    :seats="seats"
                    :busy-seat-id="busySeatId"
                    @resend="resend"
                    @revoke="revoke"
                />
            </template>
        </template>
    </div>

    <InviteEmployeeDialog
        v-model:open="inviteOpen"
        :company-name="company.name"
        :plans="plans"
        :api="api"
        @invited="onInvited"
    />
</template>
