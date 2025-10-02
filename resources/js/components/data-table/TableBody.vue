<script generic="TData, TValue" lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { TableBody, TableCell, TableRow } from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, type Table } from '@tanstack/vue-table';
import { Trash2 } from 'lucide-vue-next';

type EditableData = Record<string, unknown>;

const props = defineProps<{
    table: Table<TData>;
    columns: ColumnDef<TData, TValue>[];
    rowClick?: (row: TData) => void;
    editing?: boolean;
    addItem?: () => void;
    showRemoveButton?: boolean;
}>();

const emit = defineEmits<{
    (
        e: 'update-cell',
        payload: { rowIndex: number; accessorKey: string; value: unknown },
    ): void;
    (e: 'remove-row', payload: { row: TData; rowIndex: number }): void;
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

const handleKeyDown = (
    event: KeyboardEvent,
    rowIndex: number,
    cellIndex: number,
) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        props.addItem?.();
    } else if (event.key === 'Tab') {
        const totalRows = props.table.getRowModel().rows.length;
        const totalCells = props.columns.length;
        const isLastRow = rowIndex === totalRows - 1;
        const isLastCell = cellIndex === totalCells - 1;

        if (isLastRow && isLastCell) {
            event.preventDefault();
            props.addItem?.();
        }
    }
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
                <TableCell
                    v-for="(cell, cellIndex) in row.getVisibleCells()"
                    :key="cell.id"
                >
                    <template
                        v-if="
                            props.editing &&
                            getColumnDef(cell.column.columnDef).accessorKey &&
                            !cell.column.columnDef.cell
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
                            @keydown="
                                (event: KeyboardEvent) =>
                                    handleKeyDown(event, row.index, cellIndex)
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
                <TableCell v-if="props.showRemoveButton" class="w-12">
                    <Button
                        class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                        size="sm"
                        variant="ghost"
                        @click.stop="
                            emit('remove-row', {
                                row: row.original,
                                rowIndex: row.index,
                            })
                        "
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </TableCell>
            </TableRow>
        </template>
        <template v-else>
            <TableRow>
                <TableCell
                    :colspan="
                        props.columns.length + (props.showRemoveButton ? 1 : 0)
                    "
                    class="h-24 text-center"
                >
                    No results.
                </TableCell>
            </TableRow>
        </template>
    </TableBody>
</template>
