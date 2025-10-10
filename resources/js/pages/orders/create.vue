<script lang="ts" setup>
import ContactInfoCard from '@/components/ContactInfoCard.vue';
import { ExcelTable } from '@/components/excel-table';
import SearchSelector from '@/components/SearchSelector.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import orders from '@/routes/orders/index';
import products from '@/routes/products/index';
import { Client, Supplier } from '@/types/orders';
import { Head, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, h, nextTick, ref } from 'vue';

interface ProductItem {
    id: number;
    name: string;
    sku: string;
    price: string;
    stock: string;
    available_stock?: number;
}

interface ProductSearchResult {
    id: number;
    name: string;
    sku: string;
    price: number;
    stock: number;
}

interface StockWarning {
    productName: string;
    currentStock: number;
    orderedQuantity: number;
    resultingStock: number;
}

const breadcrumbs = [
    {
        title: 'Orders',
        href: orders.index().url,
    },
    {
        title: 'Create Order',
        href: '',
    },
];

const tableRef = ref<{ focusCell: (row: number, col: number) => void } | null>(
    null,
);

const selectedClientData = ref<Client | Supplier | null>(null);

const contactForm = useForm({
    name: '',
    contact_email: '',
    contact_phone: '',
    address: '',
});

const orderForm = useForm({
    client_id: null as number | null,
    supplier_id: null as number | null,
});

const productsForm = useForm({
    products: [] as ProductItem[],
});

const findAndFocusExistingProduct = async (
    productId: number,
): Promise<number | null> => {
    const existingIndex = productsForm.products.findIndex(
        (p) => p.id === productId,
    );

    if (existingIndex === -1) {
        return null;
    }

    await nextTick();
    if (tableRef.value) {
        tableRef.value.focusCell(existingIndex, 0);
    }
    return existingIndex;
};

const checkStockWarnings = (
    orderProducts: Array<{ id: number; price: number; stock: number }>,
): StockWarning[] => {
    const warnings: StockWarning[] = [];
    const isClientOrder = Boolean(orderForm.client_id);

    if (!isClientOrder) {
        return warnings;
    }

    orderProducts.forEach((product) => {
        const productData = productsForm.products.find(
            (p) => p.id === product.id,
        );
        if (!productData?.available_stock) return;

        const resultingStock = productData.available_stock - product.stock;

        if (resultingStock < 0) {
            warnings.push({
                productName: productData.name,
                currentStock: productData.available_stock,
                orderedQuantity: product.stock,
                resultingStock,
            });
        }
    });

    return warnings;
};

const saveAll = () => {
    if (!orderForm.client_id && !orderForm.supplier_id) {
        alert('Please select a client or supplier');
        return;
    }

    if (!productsForm.products || productsForm.products.length === 0) {
        alert('Please add at least one product');
        return;
    }

    const orderProducts = productsForm.products
        .filter((p) => p.id)
        .map((product) => ({
            id: product.id,
            price: parseFloat(product.price) || 0,
            stock: parseInt(product.stock) || 0,
        }));

    if (orderProducts.length === 0) {
        alert('Please select valid products');
        return;
    }

    const warnings = checkStockWarnings(orderProducts);
    if (warnings.length > 0) {
        const warningMessage = warnings
            .map(
                (w) =>
                    `• ${w.productName}: Current stock ${w.currentStock}, will become ${w.resultingStock}`,
            )
            .join('\n');

        const confirmed = confirm(
            `⚠️ Warning: The following products will have negative stock:\n\n${warningMessage}\n\nDo you want to continue anyway?`,
        );

        if (!confirmed) {
            return;
        }
    }

    const hasContactChanged =
        selectedClientData.value &&
        (selectedClientData.value.name !== contactForm.name ||
            selectedClientData.value.contact_email !==
                contactForm.contact_email ||
            selectedClientData.value.contact_phone !==
                contactForm.contact_phone ||
            selectedClientData.value.address !== contactForm.address);

    createOrder(orderProducts, hasContactChanged ?? false);
};

const createOrder = (
    orderProducts: Array<{ id: number; price: number; stock: number }>,
    hasContactChanged: boolean,
) => {
    const orderData: any = {
        order_products: orderProducts,
    };

    if (orderForm.client_id) {
        orderData.client_id = orderForm.client_id;
    } else if (orderForm.supplier_id) {
        orderData.supplier_id = orderForm.supplier_id;
    }

    if (hasContactChanged) {
        orderData.contact_info = {
            name: contactForm.name,
            contact_email: contactForm.contact_email,
            contact_phone: contactForm.contact_phone,
            address: contactForm.address,
        };
    }

    orderForm.transform(() => orderData).post(orders.store().url);
};

const resetAll = () => {
    contactForm.reset();
    orderForm.reset();
    productsForm.reset();
};

const calculatedTotal = computed(() => {
    if (!productsForm.products || productsForm.products.length === 0) return 0;
    return productsForm.products.reduce((total, product) => {
        const price = parseFloat(product.price) || 0;
        const stock = parseFloat(product.stock) || 0;
        return total + price * stock;
    }, 0);
});

const formattedTotal = computed(() => {
    return formatCurrency(calculatedTotal.value);
});

const updateProduct = (
    rowIndex: number,
    field: keyof ProductItem,
    value: string | number,
) => {
    const newData = [...productsForm.products];
    if (rowIndex < newData.length) {
        newData[rowIndex] = { ...newData[rowIndex], [field]: value };
        productsForm.products = newData;
    } else {
        const newRow: ProductItem = {
            id: 0,
            name: '',
            sku: '',
            price: '0',
            stock: '0',
            available_stock: undefined,
            [field]: value,
        };
        newData.push(newRow);
        productsForm.products = newData;
    }
};

const columns: ColumnDef<ProductItem>[] = [
    {
        accessorKey: 'name',
        header: 'Product Name',
        cell: ({ row }) => {
            return h(SearchSelector, {
                key: `name-${row.index}-${row.original.id || 'new'}`,
                url: products.search().url,
                modelValue: row.original.id || null,
                defaultValue: row.original.name || '',
                placeholder: 'Search products...',
                displayField: 'name',
                secondaryFields: ['sku'],
                idField: 'id',
                responseKey: 'products',
                searchParam: 'name',
                onSelect: async (item: Record<string, unknown> | null) => {
                    if (item) {
                        const productItem =
                            item as unknown as ProductSearchResult;
                        const existingIndex = await findAndFocusExistingProduct(
                            productItem.id,
                        );

                        if (
                            existingIndex !== null &&
                            existingIndex !== row.index
                        ) {
                            return;
                        }

                        const newData = [...productsForm.products];
                        const updatedRow: ProductItem = {
                            id: productItem.id,
                            name: productItem.name || '',
                            sku: productItem.sku || '',
                            price: String(productItem.price || 0),
                            stock: '0',
                            available_stock: productItem.stock,
                        };

                        if (row.index < newData.length) {
                            newData[row.index] = updatedRow;
                        } else {
                            newData.push(updatedRow);
                        }

                        productsForm.products = newData;
                    }
                },
            });
        },
    },
    {
        accessorKey: 'sku',
        header: 'SKU',
        cell: ({ row }) => {
            return h(SearchSelector, {
                key: `sku-${row.index}-${row.original.id || 'new'}`,
                url: products.search().url,
                modelValue: row.original.id || null,
                defaultValue: row.original.sku || '',
                placeholder: 'Search by SKU...',
                displayField: 'sku',
                secondaryFields: ['name'],
                idField: 'id',
                responseKey: 'products',
                searchParam: 'sku',
                onSelect: async (item: Record<string, unknown> | null) => {
                    if (item) {
                        const productItem =
                            item as unknown as ProductSearchResult;
                        const existingIndex = await findAndFocusExistingProduct(
                            productItem.id,
                        );

                        if (
                            existingIndex !== null &&
                            existingIndex !== row.index
                        ) {
                            return;
                        }

                        const newData = [...productsForm.products];
                        const updatedRow: ProductItem = {
                            id: productItem.id,
                            name: productItem.name || '',
                            sku: productItem.sku || '',
                            price: String(productItem.price || 0),
                            stock: '0',
                            available_stock: productItem.stock,
                        };

                        if (row.index < newData.length) {
                            newData[row.index] = updatedRow;
                        } else {
                            newData.push(updatedRow);
                        }

                        productsForm.products = newData;
                    }
                },
            });
        },
    },
    {
        accessorKey: 'price',
        header: 'Price',
        cell: ({ row }) => {
            return h(Input, {
                key: `price-${row.index}-${row.original.id || 'new'}`,
                type: 'number',
                modelValue: row.original.price || '',
                class: 'h-8',
                'onUpdate:modelValue': (value: string | number) => {
                    updateProduct(row.index, 'price', String(value));
                },
            });
        },
    },
    {
        accessorKey: 'stock',
        header: 'Quantity',
        cell: ({ row }) => {
            const orderedQty = parseFloat(row.original.stock) || 0;
            const availableQty = row.original.available_stock ?? null;
            const isOverStock =
                availableQty !== null && orderedQty > availableQty;

            return h('div', { class: 'space-y-1' }, [
                h(Input, {
                    key: `stock-${row.index}-${row.original.id || 'new'}`,
                    type: 'number',
                    modelValue: row.original.stock || '',
                    class: isOverStock ? 'h-8 border-red-500' : 'h-8',
                    'onUpdate:modelValue': (value: string | number) => {
                        updateProduct(row.index, 'stock', String(value));
                    },
                }),
                availableQty !== null
                    ? h(
                          'p',
                          {
                              class: `text-xs ${isOverStock ? 'text-red-600 font-medium' : 'text-muted-foreground'}`,
                          },
                          `Available: ${availableQty}`,
                      )
                    : null,
            ]);
        },
    },
];
</script>

<template>
    <Head title="Create Order" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4">
            <ContactInfoCard
                :contact="contactForm"
                :order="null"
                :selected-client-data="selectedClientData"
                order-type="client"
                @update:contact="
                    (value) => {
                        contactForm.name = value.name;
                        contactForm.contact_email = value.contact_email;
                        contactForm.contact_phone = value.contact_phone;
                        contactForm.address = value.address;
                    }
                "
                @update:client-id="
                    (value: number | null, data?: Client | Supplier | null) => {
                        orderForm.client_id = value;
                        contactForm.name = data?.name || '';
                        orderForm.supplier_id = null;
                        selectedClientData = data ?? null;
                        if (data) {
                            contactForm.contact_email =
                                data.contact_email || '';
                            contactForm.contact_phone =
                                data.contact_phone || '';
                            contactForm.address = data.address || '';
                        }
                    }
                "
            />

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between py-3"
                >
                    <CardTitle class="text-lg">Products</CardTitle>
                    <div class="flex items-center gap-3">
                        <div class="text-sm font-medium">
                            Total:
                            <span class="text-base font-semibold">
                                {{ formattedTotal }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="resetAll"
                            >
                                Reset
                            </Button>
                            <Button type="button" size="sm" @click="saveAll"
                                >Create Order</Button
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pb-4">
                    <ExcelTable
                        ref="tableRef"
                        :columns="columns"
                        :data="productsForm.products"
                        @update:data="
                            (newData) => (productsForm.products = newData)
                        "
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
