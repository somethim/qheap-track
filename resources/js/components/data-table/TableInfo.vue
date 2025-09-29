<script lang="ts" setup>
import type { Pagination } from '@/types';

const props = defineProps<{
    pagination: Pagination;
}>();
</script>

<template>
    <div class="text-sm text-muted-foreground">
        Showing {{ props.pagination.total }}
        {{ props.pagination.total === 1 ? 'item' : 'items' }}
    </div>
</template>
