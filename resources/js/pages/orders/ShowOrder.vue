<script lang="ts" setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Order } from '@/types/orders';
import { Head } from '@inertiajs/vue3';

const { order } = defineProps<{ order: Order }>();

const breadcrumbs = [
    {
        title: 'Orders',
        href: '/orders',
    },
    {
        title: order.order_number,
        href: '',
    },
];

// todo: add form to update order, add delete button
</script>

<template>
    <Head title="Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <span>{{ order }}</span>
        </div>
    </AppLayout>
</template>
