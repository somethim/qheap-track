import type { Pagination } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref, watch } from 'vue';
import type { QueryParams } from '@/wayfinder';
import { useDateRange } from './useDateRange';
import { useSearch } from './useSearch';
import { useSorting } from './useSorting';
import { useTableControls } from './useTableControls';

export interface DataTableConfig<TData, TValue = unknown> {
    // Data and columns
    data: TData[];
    columns: ColumnDef<TData, TValue>[];
    pagination: Pagination;

    // Initial state
    initialState?: {
        startDate?: string | null;
        endDate?: string | null;
        searchQuery?: string;
        sortBy?: string;
        sortDirection?: 'asc' | 'desc';
        type?: string;
        perPage?: string | null;
    };

    // Event handlers
    onNavigate: (query: QueryParams) => void;
    onRowClick?: (row: TData) => void;
    onAddItem?: () => void;
    onSearch?: (query: string) => void;
    onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void;
}

export function useDataTable<TData, TValue = unknown>(config: DataTableConfig<TData, TValue>) {
    const {
        data,
        columns,
        pagination,
        initialState = {},
        onNavigate,
        onRowClick,
        onAddItem,
        onSearch,
        onSort,
    } = config;

    // Internal state
    const startDate = ref<string | null>(initialState.startDate || null);
    const endDate = ref<string | null>(initialState.endDate || null);
    const sortBy = ref<string>(initialState.sortBy || '');
    const sortDirection = ref<'asc' | 'desc'>(
        initialState.sortDirection || 'desc',
    );

    // Watch for external prop changes
    watch(
        () => initialState.startDate,
        (newValue) => {
            startDate.value = newValue || null;
        },
    );

    watch(
        () => initialState.endDate,
        (newValue) => {
            endDate.value = newValue || null;
        },
    );

    watch(
        () => initialState.sortBy,
        (newValue) => {
            sortBy.value = newValue || '';
        },
    );

    watch(
        () => initialState.sortDirection,
        (newValue) => {
            sortDirection.value = newValue || 'desc';
        },
    );

    const { handlePageChange, handlePageSizeChange, handleFiltersChange } =
        useTableControls(pagination, onNavigate);

    const dateRangeControls = useDateRange(startDate, endDate);

    const searchControls = useSearch(initialState.searchQuery || '', onSearch);

    const sortingControls = useSorting();

    // Enhanced date change handlers
    const handleStartDateChange = (date: string | null) => {
        startDate.value = date;
        handleFiltersChange({ startDate: date, endDate: endDate.value });
    };

    const handleEndDateChange = (date: string | null) => {
        endDate.value = date;
        handleFiltersChange({ startDate: startDate.value, endDate: date });
    };

    const handleToday = () => {
        dateRangeControls.setToday();
        handleFiltersChange({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    };

    const handleLastWeek = () => {
        dateRangeControls.setLastWeek();
        handleFiltersChange({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    };

    const handleLastMonth = () => {
        dateRangeControls.setLastMonth();
        handleFiltersChange({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    };

    const handleLastYear = () => {
        dateRangeControls.setLastYear();
        handleFiltersChange({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    };

    const clearDates = () => {
        startDate.value = null;
        endDate.value = null;
        handleFiltersChange({ startDate: null, endDate: null });
    };

    // Handle sort changes
    const handleSort = (
        newSortBy: string,
        newSortDirection: 'asc' | 'desc',
    ) => {
        sortBy.value = newSortBy;
        sortDirection.value = newSortDirection;
        // Call the parent's onSort function to trigger navigation
        if (onSort) {
            onSort(newSortBy, newSortDirection);
        }
    };

    return {
        // State
        startDate: computed(() => startDate.value),
        endDate: computed(() => endDate.value),
        sortBy: computed(() => sortBy.value),
        sortDirection: computed(() => sortDirection.value),

        // Date range controls
        ...dateRangeControls,
        handleStartDateChange,
        handleEndDateChange,
        handleToday,
        handleLastWeek,
        handleLastMonth,
        handleLastYear,
        clearDates,

        // Pagination controls
        handlePageChange,
        handlePageSizeChange,

        // Search controls
        ...searchControls,

        // Sorting controls
        ...sortingControls,

        // Event handlers
        handleSort,
        handleFiltersChange,
        onRowClick,
        onAddItem,

        // Data
        data,
        columns,
        pagination,
    };
}
