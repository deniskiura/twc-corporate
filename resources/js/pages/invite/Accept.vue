<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import JoinCompanyController from '@/actions/App/Http/Controllers/JoinCompanyController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import type { Plan } from '@/types';

defineProps<{
    token: string;
    status: 'invited' | 'joined' | 'revoked';
    email: string;
    company: string;
    plan: Plan;
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: "You're invited",
        description: 'Set up your account to start using your credits.',
    },
});
</script>

<template>
    <Head :title="`Join ${company}`" />

    <div
        v-if="status === 'joined'"
        class="text-muted-foreground text-center text-sm"
    >
        This invite has already been used.
        <TextLink :href="login()">Log in</TextLink> instead.
    </div>

    <div
        v-else-if="status === 'revoked'"
        class="text-muted-foreground text-center text-sm"
    >
        This invite was withdrawn by {{ company }}. Ask your admin to send a new
        one.
    </div>

    <template v-else>
        <div class="bg-muted/40 rounded-lg border p-4 text-sm">
            <p>
                <span class="font-medium">{{ company }}</span> is sponsoring a
                <span class="font-medium">{{ plan.name }}</span> plan for
                <span class="font-medium">{{ email }}</span
                >.
            </p>
            <p class="text-muted-foreground mt-1">
                {{ plan.monthly_credits }} credits a month for classes and
                sessions. Your subscription starts the moment you join.
            </p>
        </div>

        <Form
            v-bind="JoinCompanyController.store.form({ sponsorship: token })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Your name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                        :passwordrules="passwordRules"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        :passwordrules="passwordRules"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <InputError :message="errors.invite" />

                <Button type="submit" class="w-full" :disabled="processing">
                    <Spinner v-if="processing" />
                    Join {{ company }}
                </Button>
            </div>
        </Form>
    </template>
</template>
