<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useDatePicker } from '@/composables/useDatePicker';
import { CalendarDate, today } from '@internationalized/date';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { ref, watch } from 'vue';

const props = defineProps<{
    value?: string | null;
    label: string;
    maxValue?: CalendarDate;
    minValue?: CalendarDate;
}>();

const emit = defineEmits<{
    'update:value': [date: string | null];
}>();

const valueRef = ref(props.value || null);

const { isOpen, dateValue, dateFormatter, handleDateChange } = useDatePicker(
    valueRef,
    (date: string | null) => emit('update:value', date),
);

watch(
    () => props.value,
    (newValue) => {
        valueRef.value = newValue || null;
    },
);
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button class="ps-3 text-start font-normal" variant="outline">
                <span>
                    {{
                        dateValue
                            ? dateFormatter.format(toDate(dateValue))
                            : label
                    }}
                </span>
                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar
                :calendar-label="label"
                :max-value="maxValue || today('UTC')"
                :min-value="minValue"
                :model-value="dateValue"
                initial-focus
                @update:model-value="handleDateChange"
            />
        </PopoverContent>
    </Popover>
</template>
