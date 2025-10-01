<script generic="TData, TValue" lang="ts" setup>
import { Input } from '@/components/ui/input';
import { TableBody, TableCell, TableRow } from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, type Table } from '@tanstack/vue-table';

type EditableData = Record<string, unknown>;

const props = defineProps<{
    table: Table<TData>;
    columns: ColumnDef<TData, TValue>[];
    rowClick?: (row: TData) => void;
    editing?: boolean;
}>();

const emit = defineEmits<{
    (
        e: 'update-cell',
        payload: { rowIndex: number; accessorKey: string; value: unknown },
    ): void;
}>();

const getColumnDef = (columnDef: ColumnDef<TData, TValue>) => {
    return columnDef as ColumnDef<TData, TValue> & {
        accessorKey?: string;
        header?: string | (() => unknown);
        meta?: { inputType?: string };
    };
};

const getRowData = (row: TData): EditableData => {
    return row as EditableData;
};

const getRowValue = (row: TData, accessorKey: string): string | number => {
    const rowData = getRowData(row);
    const value = rowData[accessorKey];

    if (typeof value === 'string' || typeof value === 'number') {
        return value;
    }

    return value != null ? String(value) : '';
};
</script>

<template>
    <TableBody>
        <template v-if="props.table.getRowModel().rows?.length">
            <TableRow
                v-for="row in props.table.getRowModel().rows"
                :key="row.id"
                :class="
                    props.editing
                        ? 'hover:bg-muted/30'
                        : 'cursor-pointer hover:bg-muted/50'
                "
                :data-state="row.getIsSelected() ? 'selected' : undefined"
                @click="!props.editing && props.rowClick?.(row.original)"
            >
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                    <template
                        v-if="
                            props.editing &&
                            getColumnDef(cell.column.columnDef).accessorKey
                        "
                    >
                        <Input
                            :model-value="
                                getRowValue(
                                    row.original,
                                    getColumnDef(cell.column.columnDef)
                                        .accessorKey!,
                                )
                            "
                            :placeholder="
                                String(
                                    getColumnDef(cell.column.columnDef)
                                        .header ?? '',
                                )
                            "
                            :type="
                                getColumnDef(cell.column.columnDef).meta
                                    ?.inputType || 'text'
                            "
                            @update:model-value="
                                (val: string | number) => {
                                    emit('update-cell', {
                                        rowIndex: row.index,
                                        accessorKey: getColumnDef(
                                            cell.column.columnDef,
                                        ).accessorKey!,
                                        value: val,
                                    });
                                }
                            "
                        />
                    </template>
                    <template v-else>
                        <FlexRender
                            :props="cell.getContext()"
                            :render="cell.column.columnDef.cell"
                        />
                    </template>
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
