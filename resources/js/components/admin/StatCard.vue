<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    label: string;
    value: string | number;
    hint?: string;
    tone?: 'warning' | 'danger';
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardDescription>{{ label }}</CardDescription>
            <CardTitle class="text-3xl">{{ value }}</CardTitle>
        </CardHeader>
        <CardContent
            v-if="hint"
            class="text-xs"
            :class="{
                'text-amber-700 dark:text-amber-400': tone === 'warning',
                'text-red-700 dark:text-red-400': tone === 'danger',
                'text-muted-foreground': !tone,
            }"
        >
            {{ hint }}
        </CardContent>
    </Card>
</template>
