<script lang="ts" setup>
import { Button } from '@/components/ui/button';

type Position = 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left';

const props = defineProps<{
    position?: Position;
    onSave?: () => void;
    onCancel?: () => void;
}>();

const positionClasses = {
    'top-right': 'flex justify-end',
    'top-left': 'flex justify-start',
    'bottom-right': 'flex justify-end',
    'bottom-left': 'flex justify-start',
};
</script>

<template>
    <div :class="positionClasses[props.position || 'top-right']">
        <div class="flex gap-2">
            <Button
                size="sm"
                variant="outline"
                @click="props.onCancel?.()"
            >
                Cancel
            </Button>
            <Button 
                size="sm" 
                @click="props.onSave?.()"
            >
                Save
            </Button>
        </div>
    </div>
</template>
