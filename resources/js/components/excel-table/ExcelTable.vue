<script
    generic="TData extends { id?: number | string }, TValue"
    lang="ts"
    setup
>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, h, nextTick, ref } from 'vue';

interface ColumnAccessor<TData, TValue> {
    accessorKey?: keyof TData;
    accessorFn?: (row: TData) => TValue;
}

interface ExcelTableProps<TData> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
}

const props = defineProps<ExcelTableProps<TData>>();

const emit = defineEmits<{
    'update:data': [data: TData[]];
}>();

const focusedCell = ref<{ row: number; col: number } | null>(null);
const cellRefs = ref<Map<string, HTMLElement>>(new Map());

const getCellId = (row: number, col: number): string => `cell-${row}-${col}`;

const getNextCell = (
    currentRow: number,
    currentCol: number,
    direction: 'next' | 'prev' | 'down',
): { row: number; col: number } | null => {
    const { columns } = props;
    const totalRows = props.data.length + 1;
    const totalCols = columns.length;

    if (direction === 'next') {
        if (currentCol < totalCols - 1) {
            return { row: currentRow, col: currentCol + 1 };
        } else if (currentRow < totalRows - 1) {
            return { row: currentRow + 1, col: 0 };
        }
    } else if (direction === 'prev') {
        if (currentCol > 0) {
            return { row: currentRow, col: currentCol - 1 };
        } else if (currentRow > 0) {
            return { row: currentRow - 1, col: totalCols - 1 };
        }
    } else if (direction === 'down') {
        if (currentRow < totalRows - 1) {
            return { row: currentRow + 1, col: currentCol };
        }
    }

    return null;
};

const focusCell = async (row: number, col: number) => {
    const cellId = getCellId(row, col);
    const cellElement = cellRefs.value.get(cellId);

    if (!cellElement) {
        return;
    }
    focusedCell.value = { row, col };
    await nextTick();

    const domElement = (cellElement as any).$el || cellElement;

    if (!domElement || typeof domElement.querySelector !== 'function') {
        return;
    }

    const input = domElement.querySelector(
        'input, [contenteditable="true"]',
    ) as HTMLElement;
    if (input) {
        input.focus();
        if (input.tagName === 'INPUT') {
            (input as HTMLInputElement).select();
        }
    }
};

defineExpose({
    focusCell,
});

const handleKeyDown = (event: KeyboardEvent, row: number, col: number) => {
    const { key, shiftKey } = event;

    if (key === 'Tab') {
        event.preventDefault();
        const nextCell = getNextCell(row, col, shiftKey ? 'prev' : 'next');
        if (nextCell) {
            focusCell(nextCell.row, nextCell.col);
        }
    } else if (key === 'Enter') {
        event.preventDefault();
        const nextCell = getNextCell(row, col, 'down');
        if (nextCell) {
            focusCell(nextCell.row, nextCell.col);
        }
    }
};

const updateCellValue = (
    row: number,
    column: ColumnDef<TData, TValue>,
    value: unknown,
) => {
    const newData = [...props.data];

    if (row < newData.length) {
        const accessorKey = (column as ColumnAccessor<TData, TValue>)
            .accessorKey;
        if (accessorKey) {
            newData[row] = {
                ...newData[row],
                [accessorKey]: value as TData[keyof TData],
            };
        }
    } else {
        const newRow = {} as TData;
        const accessorKey = (column as ColumnAccessor<TData, TValue>)
            .accessorKey;
        if (accessorKey) {
            newRow[accessorKey] = value as TData[keyof TData];
        }
        newData.push(newRow);
    }

    emit('update:data', newData);
};

const getCellValue = (
    row: number,
    column: ColumnDef<TData, TValue>,
): unknown => {
    if (row < props.data.length) {
        const accessor = column as ColumnAccessor<TData, TValue>;
        const accessorKey = accessor.accessorKey;
        if (accessorKey) {
            return props.data[row][accessorKey];
        }
        if (accessor.accessorFn) {
            return accessor.accessorFn(props.data[row]);
        }
    }
    return '';
};

const renderCell = (row: number, column: ColumnDef<TData, TValue>) => {
    if (column.cell && typeof column.cell === 'function') {
        const rowData =
            row < props.data.length ? props.data[row] : ({} as TData);
        const cellContext = {
            row: {
                original: rowData,
                index: row,
            },
            column: column,
        };
        return column.cell(cellContext as Parameters<typeof column.cell>[0]);
    }

    const value = getCellValue(row, column);
    return h('div', {
        contentEditable: true,
        class: 'min-h-[20px] focus:outline-none',
        innerHTML: value ? String(value) : '',
        onInput: (event: Event) => {
            const target = event.target as HTMLElement;
            updateCellValue(row, column, target.textContent || '');
        },
    });
};

const tableData = computed(() => {
    return [...props.data, {} as TData];
});
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead
                        v-for="column in props.columns"
                        :key="
                            column.id ||
                            String(
                                (column as ColumnAccessor<TData, TValue>)
                                    .accessorKey,
                            )
                        "
                    >
                        <span>{{
                            typeof column.header === 'function'
                                ? 'Header'
                                : column.header
                        }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(row, rowIndex) in tableData"
                    :key="rowIndex"
                    class="hover:bg-muted/50"
                >
                    <TableCell
                        v-for="(column, colIndex) in props.columns"
                        :key="`${rowIndex}-${column.id || colIndex}`"
                        :ref="
                            (el) => {
                                if (el) {
                                    cellRefs.set(
                                        getCellId(rowIndex, colIndex),
                                        el as HTMLElement,
                                    );
                                }
                            }
                        "
                        :class="{
                            'ring-2 ring-primary ring-offset-1':
                                focusedCell?.row === rowIndex &&
                                focusedCell?.col === colIndex,
                        }"
                        tabindex="0"
                        @click="focusCell(rowIndex, colIndex)"
                        @keydown="handleKeyDown($event, rowIndex, colIndex)"
                    >
                        <component
                            :is="renderCell(rowIndex, column)"
                            @keydown="handleKeyDown($event, rowIndex, colIndex)"
                        />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
