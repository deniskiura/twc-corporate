<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { ApiError } from '@/lib/api';
import { formatMoney } from '@/lib/format';
import type { TeamApi } from '@/lib/teamApi';
import type { Invitation, Plan } from '@/types';

const props = defineProps<{
    companyName: string;
    plans: Plan[];
    api: TeamApi;
}>();

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    invited: [invitation: Invitation];
}>();

const email = ref('');
const planId = ref(String(props.plans[0]?.id ?? ''));
const submitting = ref(false);
const errors = ref<{ email?: string; plan_id?: string; general?: string }>({});

const selectedPlan = computed(() =>
    props.plans.find((plan) => String(plan.id) === planId.value),
);

watch(open, (isOpen) => {
    if (isOpen) {
        email.value = '';
        errors.value = {};
    }
});

async function submit() {
    submitting.value = true;
    errors.value = {};

    try {
        const { data } = await props.api.invite(
            email.value,
            Number(planId.value),
        );

        emit('invited', data);
        open.value = false;
    } catch (error) {
        if (error instanceof ApiError && error.status === 422) {
            errors.value = {
                email: error.fieldError('email'),
                plan_id: error.fieldError('plan_id'),
            };
        } else {
            errors.value = {
                general:
                    error instanceof ApiError
                        ? error.message
                        : 'Could not create the invite. Please try again.',
            };
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Invite an employee</DialogTitle>
                <DialogDescription>
                    They get a link to join {{ companyName }}'s plan. Billing
                    for the seat only starts once they log in.
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-5" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="invite-email">Work email</Label>
                    <Input
                        id="invite-email"
                        v-model="email"
                        type="email"
                        required
                        autofocus
                        autocomplete="off"
                        placeholder="name@company.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="invite-plan">Plan</Label>
                    <Select v-model="planId">
                        <SelectTrigger id="invite-plan" class="w-full">
                            <SelectValue placeholder="Choose a plan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="plan in plans"
                                :key="plan.id"
                                :value="String(plan.id)"
                            >
                                {{ plan.name }} &middot;
                                {{ plan.monthly_credits }} credits a month
                                &middot;
                                {{
                                    formatMoney(
                                        plan.monthly_price,
                                        plan.currency,
                                    )
                                }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="selectedPlan"
                        class="text-muted-foreground text-xs"
                    >
                        {{ companyName }} pays
                        {{
                            formatMoney(
                                selectedPlan.monthly_price,
                                selectedPlan.currency,
                            )
                        }}
                        a month for this seat, prorated for the first month.
                    </p>
                    <InputError :message="errors.plan_id" />
                </div>

                <InputError :message="errors.general" />

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="submitting">
                        <Spinner v-if="submitting" />
                        Create invite
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
