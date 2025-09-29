<script generic="TData, TValue" lang="ts" setup>
import type { ColumnDef } from '@tanstack/vue-table';
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
import { useDateRange } from '@/composables/useDateRange';
import { useTableControls } from '@/composables/useTableControls';
import { Pagination } from '@/types';
import { parseDate } from '@internationalized/date';
import { Plus } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';
import DatePicker from './DatePicker.vue';
import DatePresets from './DatePresets.vue';
import SortableHeader from './SortableHeader.vue';
import TableBody from './TableBody.vue';
import TableHeader from './TableHeader.vue';

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
}>();

const startDate = ref<string | null>(props.startDate || null);
const endDate = ref<string | null>(props.endDate || null);
const searchQuery = ref<string>(props.searchQuery || '');

watch(
    () => props.startDate,
    (newValue) => {
        startDate.value = newValue || null;
    },
);

watch(
    () => props.endDate,
    (newValue) => {
        endDate.value = newValue || null;
    },
);

watch(
    () => props.searchQuery,
    (newValue) => {
        searchQuery.value = newValue || '';
    },
);

watch(searchQuery, (newValue) => {
    if (props.search) {
        props.search(newValue);
    }
});

const { handlePageChange, handlePageSizeChange, handleFiltersChange } =
    useTableControls(props.pagination);
const {
    startDateMax,
    endDateMin,
    hasBothDates,
    setToday,
    setLastWeek,
    setLastMonth,
    setLastYear,
} = useDateRange(startDate, endDate);

const clearDates = () => {
    startDate.value = null;
    endDate.value = null;
    handleFiltersChange({ startDate: null, endDate: null });
};

const handleStartDateChange = (date: string | null) => {
    startDate.value = date;
    if (
        endDate.value &&
        date &&
        parseDate(date).compare(parseDate(endDate.value)) > 0
    ) {
        endDate.value = null;
    }
    handleFiltersChange({ startDate: date, endDate: endDate.value });
};

const handleEndDateChange = (date: string | null) => {
    endDate.value = date;
    if (
        startDate.value &&
        date &&
        parseDate(startDate.value).compare(parseDate(date)) > 0
    ) {
        startDate.value = null;
    }
    handleFiltersChange({ startDate: startDate.value, endDate: date });
};

const handleToday = () => {
    setToday();
    handleFiltersChange({ startDate: startDate.value, endDate: endDate.value });
};

const handleLastWeek = () => {
    setLastWeek();
    handleFiltersChange({ startDate: startDate.value, endDate: endDate.value });
};

const handleLastMonth = () => {
    setLastMonth();
    handleFiltersChange({ startDate: startDate.value, endDate: endDate.value });
};

const handleLastYear = () => {
    setLastYear();
    handleFiltersChange({ startDate: startDate.value, endDate: endDate.value });
};

const processedColumns = computed(() => {
    return props.columns.map((column) => {
        if (
            (column as any).enableSorting &&
            typeof column.header === 'string'
        ) {
            const title = column.header;
            return {
                ...column,
                enableSorting: true,
                header: ({ column: tableColumn }: { column: any }) => {
                    return h(SortableHeader, {
                        column: tableColumn,
                        title,
                        sortBy: props.sortBy,
                        sortDirection: props.sortDirection,
                        onSort: props.onSort,
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
    onSortingChange: (updaterOrValue) => {
        if (typeof updaterOrValue === 'function') {
            const currentSorting =
                props.sortBy && props.sortDirection
                    ? [
                          {
                              id: props.sortBy,
                              desc: props.sortDirection === 'desc',
                          },
                      ]
                    : [];
            const newSorting = updaterOrValue(currentSorting);
            if (newSorting.length > 0 && props.onSort) {
                const sort = newSorting[0];
                props.onSort(sort.id, sort.desc ? 'desc' : 'asc');
            }
        }
    },
    state: {
        pagination: {
            pageIndex: props.pagination.currentPage - 1,
            pageSize: props.pagination.perPage,
        },
        sorting:
            props.sortBy && props.sortDirection
                ? [{ id: props.sortBy, desc: props.sortDirection === 'desc' }]
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
                    :value="startDate"
                    label="Start Date"
                    @update:value="handleStartDateChange"
                />
                <DatePicker
                    :min-value="endDateMin"
                    :value="endDate"
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
                    v-model="searchQuery"
                    class="w-64"
                    placeholder="Search..."
                />
                <Button v-if="addItem" size="sm" @click="addItem">
                    <Plus class="mr-2 h-4 w-4" />
                    Add
                </Button>
            </div>
        </div>

        <!-- Date presets left, Pagination right -->
        <div class="flex items-center justify-between">
            <DatePresets
                :on-last-month="handleLastMonth"
                :on-last-week="handleLastWeek"
                :on-last-year="handleLastYear"
                :on-today="handleToday"
            />
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
