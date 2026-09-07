<script setup lang="ts">
import { Check, Copy, Link2, X } from '@lucide/vue';
import { ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import type { Invitation } from '@/types';

const props = defineProps<{
    invitation: Invitation;
}>();

defineEmits<{
    dismiss: [];
}>();

const copied = ref(false);

async function copy() {
    await navigator.clipboard.writeText(props.invitation.invite_url);
    copied.value = true;
    window.setTimeout(() => (copied.value = false), 2000);
}
</script>

<template>
    <Alert>
        <Link2 />
        <AlertTitle>Invite link for {{ invitation.email }}</AlertTitle>
        <AlertDescription>
            <p>
                Invite emails aren't sent yet, so share this link with them
                directly. It stops working if you resend or withdraw the invite.
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <code
                    class="bg-muted max-w-full truncate rounded px-2 py-1 text-xs"
                >
                    {{ invitation.invite_url }}
                </code>
                <Button size="sm" variant="outline" @click="copy">
                    <Check v-if="copied" />
                    <Copy v-else />
                    {{ copied ? 'Copied' : 'Copy link' }}
                </Button>
                <Button size="sm" variant="ghost" @click="$emit('dismiss')">
                    <X />
                    Dismiss
                </Button>
            </div>
        </AlertDescription>
    </Alert>
</template>
