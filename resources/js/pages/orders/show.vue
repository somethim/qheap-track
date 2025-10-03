<script lang="ts" setup>
import ContactInfoCard from '@/components/ContactInfoCard.vue';
import { ExcelTable } from '@/components/excel-table';
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { isClientOrder, Order } from '@/types/orders';
import { Head, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { ref } from 'vue';

interface ProductItem {
    id: number;
    name: string;
    sku: string;
    price: string;
    stock: string;
}

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

const isClient = isClientOrder(order);
const clientOrSupplier = isClient ? order.client : order.supplier;

const contactForm = useForm({
    contact_email: clientOrSupplier?.contact_email || '',
    contact_phone: clientOrSupplier?.contact_phone || '',
    address: clientOrSupplier?.address || '',
});

const orderForm = useForm({
    client_id: isClient ? order.client_id : null,
    supplier_id: !isClient ? order.supplier_id : null,
});

// const productsForm = useForm({
//     products: order.order_products.map((order_product) => ({
//         id: order_product.product.id,
//         name: order_product.product.name,
//         sku: order_product.product.sku,
//         price: order_product.price?.toString() || '0',
//         stock: order_product.stock?.toString() || '0',
//     })),
// });

// const saveAll = () => {
//     const combinedForm = {
//         contact: contactForm.data(),
//         order: orderForm.data(),
//         products: productsForm.data(),
//     };
//     console.log(combinedForm);
// };

// const resetAll = () => {
//     contactForm.reset();
//     orderForm.reset();
//     productsForm.reset();
// };

// Products table setup
const productsData = ref<ProductItem[]>(
    order.order_products.map((order_product) => ({
        id: order_product.product.id,
        name: order_product.product.name,
        sku: order_product.product.sku,
        price: order_product.price?.toString() || '0',
        stock: order_product.stock?.toString() || '0',
    })),
);

const columns: ColumnDef<ProductItem>[] = [
    {
        accessorKey: 'name',
        header: 'Product Name',
    },
    {
        accessorKey: 'sku',
        header: 'SKU',
    },
    {
        accessorKey: 'price',
        header: 'Price',
    },
    {
        accessorKey: 'stock',
        header: 'Stock',
    },
];
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <ContactInfoCard
                    :contact="contactForm"
                    @update:contact="
                        (value) => Object.assign(contactForm, value)
                    "
                />
                <OrderDetailsCard
                    :order="order"
                    @update:client-id="
                        (value) => {
                            isClient
                                ? (orderForm.client_id = value)
                                : (orderForm.supplier_id = value);
                        }
                    "
                />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Products</CardTitle>
                </CardHeader>
                <CardContent>
                    <ExcelTable
                        :columns="columns"
                        :data="productsData"
                        @update:data="(newData) => (productsData = newData)"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
