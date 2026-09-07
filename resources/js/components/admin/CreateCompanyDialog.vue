<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import CompaniesController from '@/actions/App/Http/Controllers/Admin/CompaniesController';
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
import { Spinner } from '@/components/ui/spinner';

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add a company</DialogTitle>
                <DialogDescription>
                    Creates the company and its first admin. The admin is
                    emailed a link to choose a password, then they invite their
                    team.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="CompaniesController.store.form()"
                v-slot="{ errors, processing }"
                class="grid gap-5"
            >
                <div class="grid gap-2">
                    <Label for="company-name">Company name</Label>
                    <Input
                        id="company-name"
                        name="name"
                        required
                        autofocus
                        placeholder="Acme Logistics"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="admin-name">Admin's name</Label>
                    <Input
                        id="admin-name"
                        name="admin_name"
                        required
                        placeholder="Full name"
                    />
                    <InputError :message="errors.admin_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="admin-email">Admin's work email</Label>
                    <Input
                        id="admin-email"
                        name="admin_email"
                        type="email"
                        required
                        placeholder="name@company.com"
                    />
                    <InputError :message="errors.admin_email" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <Spinner v-if="processing" />
                        Create company
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
