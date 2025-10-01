<script lang="ts" setup>
import ContactInfoCard from '@/components/ContactInfoCard.vue';
import { DataTable } from '@/components/data-table';
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { isClientOrder, Order } from '@/types/orders';
import { Head, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';

type ProductRow = (typeof productsForm.products)[number];

interface UpdateCellPayload {
    rowIndex: number;
    accessorKey: string;
    value: unknown;
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
    contact_email: clientOrSupplier.contact_email || '',
    contact_phone: clientOrSupplier.contact_phone || '',
    address: clientOrSupplier.address || '',
});

const orderForm = useForm({
    client_id: isClient ? order.client_id : null,
    supplier_id: !isClient ? order.supplier_id : null,
});

const productsForm = useForm({
    products: order.products.map((product) => ({
        id: product.id,
        name: product.name,
        sku: product.sku,
        description: product.description || '',
        price: product.price.toString(),
        stock_quantity: product.stock_quantity.toString(),
    })),
});

const saveAll = () => {
    const combinedForm = useForm({
        contact: {
            contact_email: contactForm.contact_email,
            contact_phone: contactForm.contact_phone,
            address: contactForm.address,
        },
        order: {
            client_id: orderForm.client_id,
            supplier_id: orderForm.supplier_id,
        },
        products: productsForm.products,
    });

    combinedForm.patch(`/orders/${order.id}`, {
        preserveScroll: true,
    });
};

const resetAll = () => {
    contactForm.reset();
    orderForm.reset();
    productsForm.reset();
};

const handleProductUpdate = (payload: UpdateCellPayload) => {
    const products = productsForm.products;
    if (payload.rowIndex < 0 || payload.rowIndex >= products.length) return;
    const product = products[payload.rowIndex];
    if (!product || !(payload.accessorKey in product)) return;
    (product as Record<string, unknown>)[payload.accessorKey] = payload.value;
};

const productColumns: ColumnDef<ProductRow>[] = [
    {
        accessorKey: 'name',
        header: 'Product Name',
        cell: ({ row }) => row.original.name,
    },
    {
        accessorKey: 'sku',
        header: 'SKU',
        cell: ({ row }) => row.original.sku,
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => row.original.description || 'N/A',
    },
    {
        accessorKey: 'price',
        header: 'Price',
        meta: { inputType: 'number' },
        cell: ({ row }) => `$${Number(row.original.price).toFixed(2)}`,
    },
    {
        accessorKey: 'stock_quantity',
        header: 'Stock',
        meta: { inputType: 'number' },
        cell: ({ row }) => row.original.stock_quantity,
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
                    <DataTable
                        :columns="productColumns"
                        :data="productsForm.products"
                        :editing="true"
                        :editing-buttons-position="'bottom-right'"
                        :on-cancel="resetAll"
                        :on-save-all="saveAll"
                        :show-date-controls="false"
                        :show-editing-buttons="true"
                        :show-pagination="false"
                        :show-search="false"
                        @update-cell="handleProductUpdate"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
