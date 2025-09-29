<script generic="TData" lang="ts" setup>
import { TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { FlexRender, type Table } from '@tanstack/vue-table';

const { table } = defineProps<{ table: Table<TData> }>();
</script>

<template>
    <TableHeader>
        <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
        >
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
                <FlexRender
                    v-if="!header.isPlaceholder"
                    :props="header.getContext()"
                    :render="header.column.columnDef.header"
                />
            </TableHead>
        </TableRow>
    </TableHeader>
</template>
