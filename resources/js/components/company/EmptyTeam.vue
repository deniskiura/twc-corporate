<script setup lang="ts">
import { UserPlus, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineEmits<{
    invite: [];
}>();
</script>

<template>
    <div
        class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed px-6 py-16 text-center"
    >
        <div
            class="bg-muted mb-4 flex size-12 items-center justify-center rounded-full"
        >
            <Users class="text-muted-foreground size-6" />
        </div>
        <h3 class="text-lg font-medium">No employees yet</h3>
        <p class="text-muted-foreground mt-1 max-w-md text-sm">
            Invite your team by work email. A seat only starts billing once the
            employee logs in, so there's no cost to inviting everyone at once.
        </p>
        <Button class="mt-6" @click="$emit('invite')">
            <UserPlus />
            Invite your first employee
        </Button>
    </div>
</template>
