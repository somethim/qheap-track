<script lang="ts" setup>
import type { Table } from '@tanstack/vue-table';

const { table } = defineProps<{
    table: Table<any>;
}>();
</script>

<template>
    <div class="flex-1 text-sm text-muted-foreground">
        {{ table.getFilteredRowModel().rows.length }} row(s) total.
    </div>
</template>
