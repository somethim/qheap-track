<script generic="TData" lang="ts" setup>
import { Pagination } from '@/types';
import type { Table } from '@tanstack/vue-table';
import { computed } from 'vue';
import PageSizeSelector from './PageSizeSelector.vue';
import PaginationControls from './PaginationControls.vue';

const props = defineProps<{
    table: Table<TData>;
    pagination: Pagination;
}>();

const emit = defineEmits<{
    'page-change': [page: number];
    'page-size-change': [pageSize: number];
}>();

const currentPage = computed(() => props.pagination.currentPage);
const totalPages = computed(() => props.pagination.lastPage);
const pageSize = computed(() => props.pagination.perPage);
</script>

<template>
    <div class="flex items-center justify-end-safe px-2 py-3">
        <div class="flex items-center space-x-3 lg:space-x-8">
            <PageSizeSelector
                :page-size="pageSize"
                @page-size-change="
                    (newPageSize: number) =>
                        emit('page-size-change', newPageSize)
                "
            />
            <div class="flex items-center justify-center text-sm font-medium">
                Page {{ currentPage }} of {{ totalPages }}
            </div>
            <PaginationControls
                :pagination="props.pagination"
                @page-change="(page: number) => emit('page-change', page)"
            />
        </div>
    </div>
</template>
