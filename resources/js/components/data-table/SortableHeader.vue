<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';
import type { Column } from '@tanstack/vue-table';

interface Props {
    column: Column<any>;
    title: string;
    sortBy?: string;
    sortDirection?: 'asc' | 'desc';
    onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void;
}

const props = defineProps<Props>();

const getSortIcon = () => {
    const isCurrentlySorted = props.sortBy === props.column.id;
    if (!isCurrentlySorted) {
        return ArrowUpDown;
    }
    return props.sortDirection === 'asc' ? ArrowUp : ArrowDown;
};

const handleSort = () => {
    if (props.onSort) {
        const isCurrentlySorted = props.sortBy === props.column.id;
        const newDirection = isCurrentlySorted && props.sortDirection === 'asc' ? 'desc' : 'asc';
        props.onSort(props.column.id, newDirection);
    } else {
        props.column.toggleSorting(props.sortDirection === 'asc');
    }
};
</script>

<template>
    <Button
        variant="ghost"
        @click="handleSort"
    >
        {{ title }}
        <component :is="getSortIcon()" class="ml-2 h-4 w-4" />
    </Button>
</template>
