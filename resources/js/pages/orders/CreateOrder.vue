<script lang="ts" setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Orders',
        href: '/orders',
    },
    {
        title: 'Create Order',
        href: '',
    },
];
</script>

<template>
    <Head title="Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <span>Hi from create order</span>
        </div>
    </AppLayout>
</template>
