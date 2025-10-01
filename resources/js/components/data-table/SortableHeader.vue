<script generic="TData, TValue" lang="ts" setup>
import { Button } from '@/components/ui/button';
import { useSorting } from '@/composables/useSorting';
import type { Column } from '@tanstack/vue-table';
import { computed } from 'vue';

interface Props<TData = unknown, TValue = unknown> {
    column: Column<TData, TValue>;
    title: string;
    sortBy?: string;
    sortDirection?: 'asc' | 'desc';
    onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void;
}

const props = defineProps<Props<TData, TValue>>();

const { getSortIcon, handleSort } = useSorting<TData, TValue>();

const onSortClick = () => {
    handleSort(props.column, props.sortBy, props.sortDirection, props.onSort);
};

const sortIcon = computed(() =>
    getSortIcon(props.column, props.sortBy, props.sortDirection),
);
</script>

<template>
    <Button variant="ghost" @click="onSortClick">
        {{ title }}
        <component :is="sortIcon" class="ml-2 h-4 w-4" />
    </Button>
</template>
