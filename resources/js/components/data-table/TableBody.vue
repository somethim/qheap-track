<script generic="TData, TValue" lang="ts" setup>
import { TableBody, TableCell, TableRow } from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, type Table } from '@tanstack/vue-table';

const props = defineProps<{
    table: Table<TData>;
    columns: ColumnDef<TData, TValue>[];
    rowClick?: (row: TData) => void;
    addItem?: () => void;
}>();
</script>

<template>
    <TableBody>
        <template v-if="props.table.getRowModel().rows?.length">
            <TableRow
                v-for="row in props.table.getRowModel().rows"
                :key="row.id"
                class="cursor-pointer hover:bg-muted/50"
                :data-state="row.getIsSelected() ? 'selected' : undefined"
                @click="props.rowClick?.(row.original)"
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
                <TableCell
                    :colspan="props.columns.length"
                    class="h-24 text-center"
                >
                    No results.
                </TableCell>
            </TableRow>
        </template>
    </TableBody>
</template>
