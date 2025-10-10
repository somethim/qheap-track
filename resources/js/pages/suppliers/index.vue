<script lang="ts" setup>
import { DataTable } from '@/components/data-table';
import AppLayout from '@/layouts/AppLayout.vue';
import suppliers from '@/routes/suppliers/index';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { Supplier } from '@/types/orders';
import type { QueryParams } from '@/wayfinder';
import { Head, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { computed, h, onMounted } from 'vue';

const props = defineProps<PaginatedData<Supplier>>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Suppliers',
        href: suppliers.index().url,
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

const columns: ColumnDef<Supplier>[] = [
    {
        id: 'name',
        accessorKey: 'name',
        enableSorting: true,
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => {
            return h(
                'div',
                {
                    class: 'text-left font-medium',
                },
                row.original.name,
            );
        },
    },
    {
        id: 'contact_email',
        accessorKey: 'contact_email',
        enableSorting: true,
        header: 'Email',
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'text-muted-foreground' },
                row.original.contact_email || '—',
            );
        },
    },
    {
        id: 'contact_phone',
        accessorKey: 'contact_phone',
        enableSorting: true,
        header: 'Phone',
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'text-muted-foreground' },
                row.original.contact_phone || '—',
            );
        },
    },
    {
        id: 'order_count',
        accessorKey: 'orders_count',
        enableSorting: true,
        header: 'Orders',
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'text-left font-mono' },
                row.original.orders_count ?? 0,
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

const showSupplier = (row: Supplier) => {
    router.visit(suppliers.show(row.id).url);
};

const addSupplier = () => {
    router.visit(suppliers.create().url);
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
        suppliers.index.url({ query }),
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
                suppliers.index().url,
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
    <Head title="Suppliers" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <DataTable
            :add-item="addSupplier"
            :columns="columns"
            :data="props.items"
            :end-date="currentEndDate"
            :on-navigate="handleNavigate"
            :on-sort="handleSort"
            :pagination="props.pagination"
            :row-click="showSupplier"
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
