import { parseDate, today } from '@internationalized/date';
import { computed, type Ref } from 'vue';

export function useDateRange(
    startDate: Ref<string | null>,
    endDate: Ref<string | null>,
) {
    const startDateValue = computed(() =>
        startDate.value ? parseDate(startDate.value) : undefined,
    );

    const endDateValue = computed(() =>
        endDate.value ? parseDate(endDate.value) : undefined,
    );

    const startDateMax = computed(() => endDateValue.value || today('UTC'));
    const endDateMin = computed(() => startDateValue.value);

    const clearDates = () => {
        startDate.value = null;
        endDate.value = null;
    };

    const hasBothDates = computed(() => startDate.value && endDate.value);

    // Date preset calculations
    const todayDate = today('UTC');

    const setToday = () => {
        const todayStr = todayDate.toString();
        startDate.value = todayStr;
        endDate.value = todayStr;
    };

    const setLastWeek = () => {
        const weekAgo = todayDate.subtract({ days: 7 });
        startDate.value = weekAgo.toString();
        endDate.value = todayDate.toString();
    };

    const setLastMonth = () => {
        const monthAgo = todayDate.subtract({ months: 1 });
        startDate.value = monthAgo.toString();
        endDate.value = todayDate.toString();
    };

    const setLastYear = () => {
        const yearAgo = todayDate.subtract({ years: 1 });
        startDate.value = yearAgo.toString();
        endDate.value = todayDate.toString();
    };

    return {
        startDateValue,
        endDateValue,
        startDateMax,
        endDateMin,
        clearDates,
        hasBothDates,
        setToday,
        setLastWeek,
        setLastMonth,
        setLastYear,
    };
}
