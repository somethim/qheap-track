<script generic="TData, TValue" lang="ts" setup>
import { TableBody, TableCell, TableRow } from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, type Table } from '@tanstack/vue-table';

const { table, columns, rowClick } = defineProps<{
    table: Table<TData>;
    columns: ColumnDef<TData, TValue>[];
    rowClick?: (row: TData) => void;
}>();
</script>

<template>
    <TableBody>
        <template v-if="table.getRowModel().rows?.length">
            <TableRow
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                :data-state="row.getIsSelected() ? 'selected' : undefined"
                class="cursor-pointer hover:bg-muted/50"
                @click="rowClick?.(row.original)"
            >
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                    <FlexRender
                        :props="cell.getContext()"
                        :render="cell.column.columnDef.cell"
                    />
                </TableCell>
            </TableRow>
        </template>
        <template v-else>
            <TableRow>
                <TableCell :colspan="columns.length" class="h-24 text-center">
                    No results.
                </TableCell>
            </TableRow>
        </template>
    </TableBody>
</template>
