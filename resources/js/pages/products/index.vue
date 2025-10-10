<script lang="ts" setup>
import { DataTable } from '@/components/data-table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import products from '@/routes/products/index';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { Product } from '@/types/orders';
import type { QueryParams } from '@/wayfinder';
import { Head, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { computed, h, onMounted } from 'vue';

const props = defineProps<PaginatedData<Product>>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: products.index().url,
    },
];

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

const columns: ColumnDef<Product>[] = [
    {
        id: 'name',
        accessorKey: 'name',
        enableSorting: true,
        header: 'Name',
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'text-left font-medium' },
                row.original.name,
            );
        },
    },
    {
        id: 'sku',
        accessorKey: 'sku',
        enableSorting: true,
        header: 'SKU',
        cell: ({ row }) => {
            return h('span', { class: 'font-mono text-sm' }, row.original.sku);
        },
    },
    {
        id: 'price',
        accessorKey: 'price',
        enableSorting: true,
        header: 'Price',
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'text-left font-mono' },
                formatCurrency(row.original.price),
            );
        },
    },
    {
        id: 'stock',
        accessorKey: 'stock',
        enableSorting: true,
        header: 'Stock',
        cell: ({ row }) => {
            const stock = row.original.stock;
            const className =
                stock < 10
                    ? 'text-red-600 font-semibold'
                    : stock < 50
                      ? 'text-yellow-600'
                      : '';
            return h(
                'span',
                { class: `text-left ${className}` },
                stock.toString(),
            );
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

const viewProduct = (row: Product) => {
    router.visit(products.show(row.id).url);
};

const createProduct = () => {
    router.visit(products.create().url);
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

    router.visit(`${window.location.pathname}?${newParams.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleNavigate = (query: QueryParams) => {
    router.get(
        products.index.url({ query }),
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
                products.index().url,
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
    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <DataTable
            :add-item="createProduct"
            :columns="columns"
            :data="props.items"
            :end-date="currentEndDate"
            :on-navigate="handleNavigate"
            :on-sort="handleSort"
            :pagination="props.pagination"
            :row-click="viewProduct"
            :search="search"
            :search-query="currentSearchQuery"
            :show-date-controls="true"
            :show-pagination="true"
            :show-search="true"
            :sort-by="currentSortBy"
            :sort-direction="currentSortDirection"
            :start-date="currentStartDate"
        />
    </AppLayout>
</template>
