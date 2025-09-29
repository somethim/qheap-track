<script lang="ts" setup>
import { Button } from '@/components/ui/button';

const props = defineProps<{
    onToday: () => void;
    onLastWeek: () => void;
    onLastMonth: () => void;
    onLastYear: () => void;
}>();

const presets = [
    { label: 'Today', handler: props.onToday },
    { label: 'Last Week', handler: props.onLastWeek },
    { label: 'Last Month', handler: props.onLastMonth },
    { label: 'Last Year', handler: props.onLastYear },
];
</script>

<template>
    <div class="flex items-center space-x-2">
        <span class="text-sm font-medium text-muted-foreground">Quick:</span>
        <div class="flex space-x-1">
            <Button
                v-for="preset in presets"
                :key="preset.label"
                size="sm"
                variant="ghost"
                class="h-8 px-2 text-xs"
                @click="preset.handler"
            >
                {{ preset.label }}
            </Button>
        </div>
    </div>
</template>
