<script lang="ts" setup>
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import orders from '@/routes/orders';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { isClientOrder, Order } from '@/types/orders';
import { Head, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';

const props = defineProps<PaginatedData<Order>>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: orders.index().url,
    },
];

const orderType = computed(() => {
    return props.items.length > 0 && isClientOrder(props.items[0])
        ? 'client'
        : 'supplier';
});

const currentStartDate = ref<string>();
const currentEndDate = ref<string>();

watch(
    () => props,
    () => {
        const urlParams = new URLSearchParams(window.location.search);
        currentStartDate.value = urlParams.get('start_date') ?? undefined;
        currentEndDate.value = urlParams.get('end_date') ?? undefined;
    },
    { immediate: true, deep: true },
);

const columns: ColumnDef<Order>[] = [
    {
        accessorKey: 'order_number',
        header: ({ column }) => {
            const isSorted = column.getIsSorted();
            let ArrowIcon = ArrowUpDown;
            if (isSorted === 'asc') ArrowIcon = ArrowUp;
            else if (isSorted === 'desc') ArrowIcon = ArrowDown;
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(isSorted === 'asc'),
                },
                () => ['Order #', h(ArrowIcon, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const orderNumber = row.original.order_number;
            const shortNumber = orderNumber.slice(-8);
            return h(
                'div',
                {
                    class: 'text-left fo' + 'nt-mono',
                    title: orderNumber,
                },
                shortNumber,
            );
        },
    },
    {
        accessorKey: 'entity',
        accessorFn: (row) =>
            isClientOrder(row) ? row.client.name : row.supplier.name,
        header: ({ column }) => {
            const isSorted = column.getIsSorted();
            let ArrowIcon = ArrowUpDown;
            if (isSorted === 'asc') ArrowIcon = ArrowUp;
            else if (isSorted === 'desc') ArrowIcon = ArrowDown;
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(isSorted === 'asc'),
                },
                () => [
                    orderType.value === 'client' ? 'Client' : 'Supplier',
                    h(ArrowIcon, { class: 'ml-2 h-4 w-4' }),
                ],
            );
        },
        cell: ({ row }) => {
            const order = row.original;
            if (isClientOrder(order)) {
                return h('span', order.client.name);
            }
            return h('span', order.supplier.name);
        },
    },
    {
        accessorKey: 'total_amount',
        header: ({ column }) => {
            const isSorted = column.getIsSorted();
            let ArrowIcon = ArrowUpDown;
            if (isSorted === 'asc') ArrowIcon = ArrowUp;
            else if (isSorted === 'desc') ArrowIcon = ArrowDown;
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(isSorted === 'asc'),
                },
                () => ['Price', h(ArrowIcon, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const amount = Number.parseFloat(row.getValue('total_amount'));
            const formatted = new Intl.NumberFormat('sq-AL', {
                style: 'currency',
                currency: 'ALL',
            }).format(amount);

            return h('div', { class: 'text-left font-mono' }, formatted);
        },
    },
    {
        accessorKey: 'item_count',
        header: ({ column }) => {
            const isSorted = column.getIsSorted();
            let ArrowIcon = ArrowUpDown;
            if (isSorted === 'asc') ArrowIcon = ArrowUp;
            else if (isSorted === 'desc') ArrowIcon = ArrowDown;
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(isSorted === 'asc'),
                },
                () => ['Quantity', h(ArrowIcon, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            return h('span', row.original.item_count);
        },
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            const isSorted = column.getIsSorted();
            let ArrowIcon = ArrowUpDown;
            if (isSorted === 'asc') ArrowIcon = ArrowUp;
            else if (isSorted === 'desc') ArrowIcon = ArrowDown;
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(isSorted === 'asc'),
                },
                () => ['Created At', h(ArrowIcon, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-left font-mono' },
                new Date(row.original.created_at).toLocaleDateString(),
            );
        },
    },
];

const orderClick = (row: Order) => {
    router.visit(orders.show(row.id).url);
};

const addOrder = () => {
    router.visit(orders.create().url);
};
</script>

<template>
    <Head title="Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <DataTable
                :add-item="addOrder"
                :columns="columns"
                :data="props.items"
                :end-date="currentEndDate"
                :pagination="props.pagination"
                :row-click="orderClick"
                :start-date="currentStartDate"
            />
        </div>
    </AppLayout>
</template>
