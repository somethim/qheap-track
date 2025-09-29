<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    CalendarDate,
    DateFormatter,
    parseDate,
    today,
} from '@internationalized/date';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { DateValue } from 'reka-ui';
import { toDate } from 'reka-ui/date';
import { computed, ref } from 'vue';

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const props = defineProps<{
    value?: string | null;
    label: string;
    maxValue?: CalendarDate;
    minValue?: CalendarDate;
}>();

const emit = defineEmits<{
    'update:value': [date: string | null];
}>();

const isOpen = ref(false);

const dateValue = computed({
    get: () => (props.value ? parseDate(props.value) : undefined),
    set: (val) => {
        handleDateChange(val);
    },
});

const handleDateChange = (date: DateValue | undefined) => {
    if (date) {
        emit('update:value', date.toString());
    } else {
        emit('update:value', null);
    }
    isOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button class="ps-3 text-start font-normal" variant="outline">
                <span>
                    {{ dateValue ? df.format(toDate(dateValue)) : label }}
                </span>
                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar
                :calendar-label="label"
                :max-value="maxValue || today('utc')"
                :min-value="minValue"
                :model-value="dateValue"
                initial-focus
                @update:model-value="handleDateChange"
            />
        </PopoverContent>
    </Popover>
</template>
