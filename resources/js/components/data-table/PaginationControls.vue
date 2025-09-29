<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import type { Pagination } from '@/types';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    pagination: Pagination;
}>();

const emit = defineEmits<{
    'page-change': [page: number];
}>();

// Direct computed properties for pagination state
const canPreviousPage = computed(() => !!props.pagination.prevPageUrl);
const canNextPage = computed(() => props.pagination.hasMorePages);

// Direct event handlers
const handleFirstPage = () => {
    emit('page-change', 1);
};

const handlePreviousPage = () => {
    if (canPreviousPage.value && props.pagination.currentPage > 1) {
        emit('page-change', props.pagination.currentPage - 1);
    }
};

const handleNextPage = () => {
    if (canNextPage.value && props.pagination.currentPage < props.pagination.lastPage) {
        emit('page-change', props.pagination.currentPage + 1);
    }
};

const handleLastPage = () => {
    emit('page-change', props.pagination.lastPage);
};
</script>

<template>
    <div class="flex items-center space-x-2">
        <Button
            :disabled="!canPreviousPage"
            class="hidden h-8 w-8 p-0 lg:flex"
            variant="outline"
            @click="handleFirstPage"
        >
            <span class="sr-only">Go to first page</span>
            <ChevronsLeft class="h-4 w-4" />
        </Button>
        <Button
            :disabled="!canPreviousPage"
            class="h-8 w-8 p-0"
            variant="outline"
            @click="handlePreviousPage"
        >
            <span class="sr-only">Go to previous page</span>
            <ChevronLeft class="h-4 w-4" />
        </Button>
        <Button
            :disabled="!canNextPage"
            class="h-8 w-8 p-0"
            variant="outline"
            @click="handleNextPage"
        >
            <span class="sr-only">Go to next page</span>
            <ChevronRight class="h-4 w-4" />
        </Button>
        <Button
            :disabled="!canNextPage"
            class="hidden h-8 w-8 p-0 lg:flex"
            variant="outline"
            @click="handleLastPage"
        >
            <span class="sr-only">Go to last page</span>
            <ChevronsRight class="h-4 w-4" />
        </Button>
    </div>
</template>
