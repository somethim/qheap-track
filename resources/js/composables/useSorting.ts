import type { Column } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { type Component } from 'vue';

export interface SortingControls {
    getSortIcon: (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
    ) => Component;
    handleSort: (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
        onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void,
    ) => void;
    isSorted: (column: Column<any, unknown>, sortBy?: string) => boolean;
    getSortDirection: (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
    ) => 'asc' | 'desc' | undefined;
}

export function useSorting(): SortingControls {
    const getSortIcon = (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
    ) => {
        const isCurrentlySorted = sortBy === column.id;
        if (!isCurrentlySorted) {
            return ArrowUpDown;
        }
        return sortDirection === 'asc' ? ArrowUp : ArrowDown;
    };

    const handleSort = (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
        onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void,
    ) => {
        if (onSort) {
            const isCurrentlySorted = sortBy === column.id;
            const newDirection =
                isCurrentlySorted && sortDirection === 'asc' ? 'desc' : 'asc';
            onSort(column.id, newDirection);
        } else {
            column.toggleSorting(sortDirection === 'asc');
        }
    };

    const isSorted = (column: Column<any, unknown>, sortBy?: string) => {
        return sortBy === column.id;
    };

    const getSortDirection = (
        column: Column<any, unknown>,
        sortBy?: string,
        sortDirection?: 'asc' | 'desc',
    ) => {
        return isSorted(column, sortBy) ? sortDirection : undefined;
    };

    return {
        getSortIcon,
        handleSort,
        isSorted,
        getSortDirection,
    };
}
