<script lang="ts" setup>
import { DataTable } from '@/components/data-table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import orders from '@/routes/orders/index';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { isClientOrder, Order } from '@/types/orders';
import type { QueryParams } from '@/wayfinder';
import { Head, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { computed, h, onMounted } from 'vue';

const props = defineProps<PaginatedData<Order> & { type: string }>();

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

const urlParams = computed(() => new URLSearchParams(window.location.search));

const currentStartDate = computed(
    () => urlParams.value.get('start_date') ?? undefined,
);
const currentEndDate = computed(
    () => urlParams.value.get('end_date') ?? undefined,
);
const currentSearchQuery = computed(() => urlParams.value.get('search') ?? '');
const currentSortBy = computed(
    () => urlParams.value.get('sort_by') ?? 'created_at',
);
const currentSortDirection = computed(
    () => (urlParams.value.get('sort_direction') as 'asc' | 'desc') ?? 'desc',
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
        id: `${orderType.value}_name`,
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
        id: 'cost',
        accessorKey: 'cost',
        enableSorting: true,
        header: 'Price',
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-mono' }, row.original.formatted_cost);
        },
    },
    {
        id: 'stock',
        accessorKey: 'stock',
        enableSorting: true,
        header: 'stock',
        cell: ({ row }) => {
            return h('span', row.original.stock);
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

const showOrder = (row: Order) => {
    router.visit(orders.show(row.id).url);
};

const addOrder = () => {
    router.visit(orders.create.url({ query: { type: props.type || 'client' } }));
};

const handleOrderTypeChange = (newType: string) => {
    const newParams = new URLSearchParams();
    newParams.set('type', newType);
    
    router.visit(`${window.location.pathname}?${newParams.toString()}`, {
        preserveState: false,
        preserveScroll: false,
    });
};

const search = (query: string) => {
    const trimmedQuery = query.trim();
    const newParams = new URLSearchParams(window.location.search);

    if (trimmedQuery) {
        newParams.set('search', trimmedQuery);
    } else {
        newParams.delete('search');
    }

    newParams.set('page', '1');
    
    if (!newParams.has('type')) {
        newParams.set('type', props.type || 'client');
    }

    router.visit(`${window.location.pathname}?${newParams.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSort = (sortBy: string, sortDirection: 'asc' | 'desc') => {
    const newParams = new URLSearchParams(window.location.search);
    newParams.set('sort_by', sortBy);
    newParams.set('sort_direction', sortDirection);
    newParams.set('page', '1');
    
    if (!newParams.has('type')) {
        newParams.set('type', props.type || 'client');
    }

    router.visit(`${window.location.pathname}?${newParams.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleNavigate = (query: QueryParams) => {
    const queryWithType = {
        ...query,
        type: props.type || 'client',
    };
    
    router.get(
        orders.index.url({ query: queryWithType }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

onMounted(() => {
    const navigationEntry = performance.getEntriesByType(
        'navigation',
    )[0] as PerformanceNavigationTiming;
    const isPageRefresh = navigationEntry?.type === 'reload';

    if (isPageRefresh) {
        const currentParams = urlParams.value;
        const hasFilters =
            currentParams.has('search') ||
            currentParams.has('start_date') ||
            currentParams.has('end_date') ||
            currentParams.has('sort_by') ||
            currentParams.has('sort_direction') ||
            (currentParams.has('page') && currentParams.get('page') !== '1') ||
            (currentParams.has('per_page') &&
                currentParams.get('per_page') !== '10');

        if (hasFilters) {
            router.get(
                orders.index().url,
                {},
                {
                    preserveState: false,
                    preserveScroll: false,
                    replace: true,
                },
            );
        }
    }
});
</script>

<template>
    <Head title="Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Tabs :default-value="props.type || 'client'" class="w-full">
            <TabsList class="mb-4 grid w-full max-w-md grid-cols-2">
                <TabsTrigger 
                    value="client" 
                    @click="handleOrderTypeChange('client')"
                >
                    Client Orders
                </TabsTrigger>
                <TabsTrigger 
                    value="supplier"
                    @click="handleOrderTypeChange('supplier')"
                >
                    Supplier Orders
                </TabsTrigger>
            </TabsList>
            
            <TabsContent value="client" class="mt-0">
                <DataTable
                    :add-item="addOrder"
                    :columns="columns"
                    :data="props.items"
                    :end-date="currentEndDate"
                    :on-navigate="handleNavigate"
                    :on-sort="handleSort"
                    :pagination="props.pagination"
                    :row-click="showOrder"
                    :search="search"
                    :search-query="currentSearchQuery"
                    :show-date-controls="true"
                    :show-pagination="true"
                    :show-search="true"
                    :sort-by="currentSortBy"
                    :sort-direction="currentSortDirection"
                    :start-date="currentStartDate"
                />
            </TabsContent>
            
            <TabsContent value="supplier" class="mt-0">
                <DataTable
                    :add-item="addOrder"
                    :columns="columns"
                    :data="props.items"
                    :end-date="currentEndDate"
                    :on-navigate="handleNavigate"
                    :on-sort="handleSort"
                    :pagination="props.pagination"
                    :row-click="showOrder"
                    :search="search"
                    :search-query="currentSearchQuery"
                    :show-date-controls="true"
                    :show-pagination="true"
                    :show-search="true"
                    :sort-by="currentSortBy"
                    :sort-direction="currentSortDirection"
                    :start-date="currentStartDate"
                />
            </TabsContent>
        </Tabs>
    </AppLayout>
</template>
