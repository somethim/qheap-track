<script generic="TData, TValue" lang="ts" setup>
import type { Column, ColumnDef } from '@tanstack/vue-table';
import {
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';

import TablePagination from '@/components/data-table/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table } from '@/components/ui/table';
import { useDataTable } from '@/composables/useDataTable';
import { Pagination } from '@/types';
import type { QueryParams } from '@/wayfinder';
import { Plus } from 'lucide-vue-next';
import { computed, h } from 'vue';
import DatePicker from './DatePicker.vue';
import DatePresets from './DatePresets.vue';
import SortableHeader from './SortableHeader.vue';
import TableBody from './TableBody.vue';
import TableHeader from './TableHeader.vue';
import TableInfo from './TableInfo.vue';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    pagination: Pagination;
    startDate?: string;
    endDate?: string;
    searchQuery?: string;
    sortBy?: string;
    sortDirection?: 'asc' | 'desc';
    rowClick?: (row: TData) => void;
    addItem?: () => void;
    search?: (query: string) => void;
    onSort?: (sortBy: string, sortDirection: 'asc' | 'desc') => void;
    onNavigate?: (query: QueryParams) => void;
}>();

const dataTableControls = useDataTable({
    data: props.data,
    columns: props.columns,
    pagination: props.pagination,
    initialState: {
        startDate: props.startDate,
        endDate: props.endDate,
        searchQuery: props.searchQuery,
        sortBy: props.sortBy,
        sortDirection: props.sortDirection,
    },
    onNavigate: props.onNavigate || (() => {}),
    onRowClick: props.rowClick,
    onAddItem: props.addItem,
    onSearch: props.search,
    onSort: props.onSort,
});

const {
    startDate: currentStartDate,
    endDate: currentEndDate,
    searchQuery: currentSearchQuery,
    sortBy: currentSortBy,
    sortDirection: currentSortDirection,
    startDateMax,
    endDateMin,
    hasBothDates,
    handleStartDateChange,
    handleEndDateChange,
    handleToday,
    handleLastWeek,
    handleLastMonth,
    handleLastYear,
    clearDates,
    handlePageChange,
    handlePageSizeChange,
    handleSort,
} = dataTableControls;

const processedColumns = computed(() => {
    return props.columns.map((column) => {
        if (
            (column as ColumnDef<TData, TValue> & { enableSorting?: boolean })
                .enableSorting &&
            typeof column.header === 'string'
        ) {
            const title = column.header;
            return {
                ...column,
                enableSorting: true,
                header: ({
                    column: tableColumn,
                }: {
                    column: Column<TData, TValue>;
                }) => {
                    return h(SortableHeader, {
                        column: tableColumn,
                        title,
                        sortBy: currentSortBy.value,
                        sortDirection: currentSortDirection.value,
                        onSort: handleSort,
                    });
                },
            };
        }
        return column;
    });
});

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return processedColumns.value as ColumnDef<TData, TValue>[];
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    manualPagination: true,
    manualSorting: true,
    state: {
        pagination: {
            pageIndex: props.pagination.currentPage - 1,
            pageSize: props.pagination.perPage,
        },
        sorting:
            currentSortBy.value && currentSortDirection.value
                ? [
                      {
                          id: currentSortBy.value,
                          desc: currentSortDirection.value === 'desc',
                      },
                  ]
                : [],
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Top row: Date controls left, Add button right -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <DatePicker
                    :max-value="startDateMax"
                    :value="currentStartDate"
                    label="Start Date"
                    @update:value="handleStartDateChange"
                />
                <DatePicker
                    :min-value="endDateMin"
                    :value="currentEndDate"
                    label="End Date"
                    @update:value="handleEndDateChange"
                />
                <Button
                    v-if="hasBothDates"
                    size="sm"
                    variant="outline"
                    @click="clearDates"
                >
                    Clear
                </Button>
            </div>
            <div class="flex items-center space-x-2">
                <Input
                    v-model="currentSearchQuery"
                    class="w-64"
                    placeholder="Search..."
                />
                <Button v-if="props.addItem" size="sm" @click="props.addItem">
                    <Plus class="mr-2 h-4 w-4" />
                    Add
                </Button>
            </div>
        </div>

        <!-- Date presets left, Table info center, Pagination right -->
        <div class="flex items-center justify-between">
            <DatePresets
                :on-last-month="handleLastMonth"
                :on-last-week="handleLastWeek"
                :on-last-year="handleLastYear"
                :on-today="handleToday"
            />
            <TableInfo :pagination="props.pagination" />
            <TablePagination
                :pagination="props.pagination"
                :table="table"
                @page-change="handlePageChange"
                @page-size-change="handlePageSizeChange"
            />
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader :table="table" />
                <TableBody
                    :columns="props.columns"
                    :row-click="props.rowClick"
                    :table="table"
                />
            </Table>
        </div>
    </div>
</template>
