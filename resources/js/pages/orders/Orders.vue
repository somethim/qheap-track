<script lang="ts" setup>
import { DataTable } from '@/components/data-table';
import AppLayout from '@/layouts/AppLayout.vue';
import orders from '@/routes/orders';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { isClientOrder, Order } from '@/types/orders';
import { Head, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
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
const currentSearchQuery = ref<string>('');
const currentSortBy = ref<string>('created_at');
const currentSortDirection = ref<'asc' | 'desc'>('desc');

watch(
    () => props,
    () => {
        const urlParams = new URLSearchParams(window.location.search);
        currentStartDate.value = urlParams.get('start_date') ?? undefined;
        currentEndDate.value = urlParams.get('end_date') ?? undefined;
        currentSearchQuery.value = urlParams.get('search') ?? '';
        currentSortBy.value = urlParams.get('sort_by') ?? 'created_at';
        currentSortDirection.value =
            (urlParams.get('sort_direction') as 'asc' | 'desc') ?? 'desc';
    },
    { immediate: true, deep: true },
);

const columns: ColumnDef<Order>[] = [
    {
        accessorKey: 'order_number',
        enableSorting: false,
        header: () => h('div', { class: 'text-left' }, 'Order #'),
        cell: ({ row }) => {
            const orderNumber = row.original.order_number;
            const shortNumber = orderNumber.slice(-8);
            return h(
                'div',
                {
                    class: 'text-left font-mono',
                    title: orderNumber,
                },
                shortNumber,
            );
        },
    },
    {
        id: `${orderType.value}_id`,
        accessorKey: `${orderType.value}_id`,
        enableSorting: true,
        accessorFn: (row) =>
            isClientOrder(row) ? row.client.name : row.supplier.name,
        header: orderType.value === 'client' ? 'Client' : 'Supplier',
        cell: ({ row }) => {
            const order = row.original;
            if (isClientOrder(order)) {
                return h('span', order.client.name);
            }
            return h('span', order.supplier.name);
        },
    },
    {
        id: 'total_amount',
        accessorKey: 'total_amount',
        enableSorting: true,
        header: 'Price',
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
        id: 'item_count',
        accessorKey: 'item_count',
        enableSorting: true,
        header: 'Quantity',
        cell: ({ row }) => {
            return h('span', row.original.item_count);
        },
    },
    {
        id: 'created_at',
        accessorKey: 'created_at',
        enableSorting: true,
        header: 'Created At',
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

const search = (query: string) => {
    const urlParams = new URLSearchParams(window.location.search);

    if (query.trim()) {
        urlParams.set('search', query.trim());
    } else {
        urlParams.delete('search');
    }

    urlParams.set('page', '1');

    router.visit(`${window.location.pathname}?${urlParams.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSort = (sortBy: string, sortDirection: 'asc' | 'desc') => {
    const urlParams = new URLSearchParams(window.location.search);

    urlParams.set('sort_by', sortBy);
    urlParams.set('sort_direction', sortDirection);
    urlParams.set('page', '1');

    router.visit(`${window.location.pathname}?${urlParams.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
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
                :on-sort="handleSort"
                :pagination="props.pagination"
                :row-click="orderClick"
                :search="search"
                :search-query="currentSearchQuery"
                :sort-by="currentSortBy"
                :sort-direction="currentSortDirection"
                :start-date="currentStartDate"
            />
        </div>
    </AppLayout>
</template>
