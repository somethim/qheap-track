import {
    CalendarDate,
    DateFormatter,
    parseDate,
} from '@internationalized/date';
import { DateValue } from 'reka-ui';
import { computed, ref, type Ref } from 'vue';

export interface DatePickerControls {
    isOpen: Ref<boolean>;
    dateValue: Ref<CalendarDate | undefined>;
    dateFormatter: DateFormatter;
    handleDateChange: (date: DateValue | undefined) => void;
}

export function useDatePicker(
    value: Ref<string | null>,
    onUpdate: (date: string | null) => void,
): DatePickerControls {
    const isOpen = ref(false);

    const dateFormatter = new DateFormatter('en-US', {
        dateStyle: 'long',
    });

    const dateValue = computed({
        get: () => (value.value ? parseDate(value.value) : undefined),
        set: (val) => {
            handleDateChange(val);
        },
    });

    const handleDateChange = (date: DateValue | undefined) => {
        if (date) {
            onUpdate(date.toString());
        } else {
            onUpdate(null);
        }
        isOpen.value = false;
    };

    return {
        isOpen,
        dateValue,
        dateFormatter,
        handleDateChange,
    };
}
