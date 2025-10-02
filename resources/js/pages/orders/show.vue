<script lang="ts" setup>
import ContactInfoCard from '@/components/ContactInfoCard.vue';
import { DataTable } from '@/components/data-table';
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import SearchSelector from '@/components/SearchSelector.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { search as productSearch } from '@/routes/products';
import { isClientOrder, Order } from '@/types/orders';
import { Head, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

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
    contact_email: clientOrSupplier?.contact_email || '',
    contact_phone: clientOrSupplier?.contact_phone || '',
    address: clientOrSupplier?.address || '',
});

const orderForm = useForm({
    client_id: isClient ? order.client_id : null,
    supplier_id: !isClient ? order.supplier_id : null,
});

const productsForm = useForm({
    products: order.order_products.map((order_product) => ({
        id: order_product.product.id,
        name: order_product.product.name,
        sku: order_product.product.sku,
        price: order_product.price?.toString() || '0',
        stock: order_product.stock?.toString() || '0',
    })),
});

const saveAll = () => {
    const combinedForm = {
        contact: contactForm.data(),
        order: orderForm.data(),
        products: productsForm.data(),
    };
    console.log(combinedForm);
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

const addNewProduct = () => {
    productsForm.products = [
        ...productsForm.products,
        {
            id: 0,
            name: '',
            sku: '',
            price: '0',
            stock: '0',
        },
    ];
};

const handleRemoveProduct = (row: ProductRow, rowIndex: number) => {
    const filteredProducts = productsForm.products.filter(
        (_, index) => index !== rowIndex,
    );

    if (filteredProducts.length === 0) {
        filteredProducts.push({
            id: 0,
            name: '',
            sku: '',
            price: '0',
            stock: '0',
        });
    }

    productsForm.products = filteredProducts;
};

const handleProductSelection = (
    rowIndex: number,
    productId: number | string | null,
) => {
    const products = productsForm.products;
    if (rowIndex < 0 || rowIndex >= products.length || !productId) return;

    products[rowIndex] = {
        ...products[rowIndex],
        id: Number(productId),
    };
};

const productColumns: ColumnDef<ProductRow>[] = [
    {
        accessorKey: 'sku',
        header: 'SKU',
        cell: ({ row }) => {
            const rowIndex = productsForm.products.findIndex(
                (p) => p.id === row.original.id,
            );
            return h(SearchSelector, {
                modelValue: row.original.id,
                defaultValue: row.original.sku,
                url: productSearch().url,
                displayField: 'sku',
                idField: 'id',
                secondaryFields: ['name'],
                placeholder: 'Search by SKU...',
                noResultsText: 'No products found',
                responseKey: 'products',
                searchParam: 'sku',
                'onUpdate:modelValue': (value: number | string | null) => {
                    handleProductSelection(rowIndex, value);
                },
            });
        },
    },
    {
        accessorKey: 'name',
        header: 'Product Name',
        cell: ({ row }) => {
            const rowIndex = productsForm.products.findIndex(
                (p) => p.id === row.original.id,
            );
            return h(SearchSelector, {
                modelValue: row.original.id,
                defaultValue: row.original.name,
                url: productSearch().url,
                displayField: 'name',
                idField: 'id',
                secondaryFields: ['sku'],
                placeholder: 'Search by name...',
                noResultsText: 'No products found',
                responseKey: 'products',
                searchParam: 'name',
                'onUpdate:modelValue': (value: number | string | null) => {
                    handleProductSelection(rowIndex, value);
                },
            });
        },
    },
    {
        accessorKey: 'price',
        header: 'Price',
        meta: { inputType: 'number' },
        cell: ({ row }) => `$${Number(row.original.price).toFixed(2)}`,
    },
    {
        accessorKey: 'stock',
        header: 'Stock',
        meta: { inputType: 'number' },
        cell: ({ row }) => row.original.stock,
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
                        :add-item="addNewProduct"
                        :columns="productColumns"
                        :data="productsForm.products"
                        :editing="true"
                        :editing-buttons-position="'bottom-right'"
                        :on-cancel="resetAll"
                        :on-save-all="saveAll"
                        :show-date-controls="false"
                        :show-editing-buttons="true"
                        :show-pagination="false"
                        :show-remove-button="true"
                        :show-search="false"
                        @update-cell="handleProductUpdate"
                        @remove-row="handleRemoveProduct"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
