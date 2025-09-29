<script lang="ts" setup>
import { Pagination } from '@/types';
import type { Table } from '@tanstack/vue-table';
import { computed } from 'vue';
import PageSizeSelector from './PageSizeSelector.vue';
import PaginationControls from './PaginationControls.vue';

const { pagination } = defineProps<{
    table: Table<any>;
    pagination: Pagination;
}>();

const emit = defineEmits<{
    'page-change': [page: number];
    'page-size-change': [pageSize: number];
}>();

const currentPage = computed(() => pagination.currentPage);
const totalPages = computed(() => pagination.lastPage);
const pageSize = computed(() => pagination.perPage);

computed(() => pagination.total);
const canPreviousPage = computed(() => !!pagination.prevPageUrl);
const canNextPage = computed(() => pagination.hasMorePages);

const handlePageSizeChange = (newPageSize: number) => {
    emit('page-size-change', newPageSize);
};

const handlePageChange = (page: number) => {
    emit('page-change', page);
};
</script>

<template>
    <div class="flex items-center justify-end-safe px-2 py-3">
        <div class="flex items-center space-x-3 lg:space-x-8">
            <PageSizeSelector
                :page-size="pageSize"
                @page-size-change="handlePageSizeChange"
            />
            <div class="flex items-center justify-center text-sm font-medium">
                Page {{ currentPage }} of {{ totalPages }}
            </div>
            <PaginationControls
                :can-next-page="canNextPage"
                :can-previous-page="canPreviousPage"
                :current-page="currentPage"
                :total-pages="totalPages"
                @page-change="handlePageChange"
            />
        </div>
    </div>
</template>
