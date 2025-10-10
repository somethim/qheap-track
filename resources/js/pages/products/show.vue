<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/products/index';
import { BreadcrumbItem } from '@/types';
import { Product } from '@/types/orders';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';

const props = defineProps<{ product: Product }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: products.index().url,
    },
    {
        title: props.product.name,
        href: '',
    },
];

const form = useForm({
    name: props.product.name,
    price: props.product.price.toString(),
    stock: props.product.stock.toString(),
    sku: props.product.sku,
});

const submitForm = () => {
    form.put(products.update(props.product.id).url, {
        preserveScroll: true,
    });
};

const deleteProduct = () => {
    if (
        confirm(
            'Are you sure you want to delete this product? This action cannot be undone.',
        )
    ) {
        const route = products.destroy(props.product);
        router.visit(route.url, { method: route.method });
    }
};

const cancel = () => {
    form.reset();
};
</script>

<template>
    <Head :title="`Product: ${product.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader
                class="flex flex-row items-center justify-between py-3"
            >
                <CardTitle class="text-lg">Product Information</CardTitle>
                <Button
                    variant="destructive"
                    size="sm"
                    @click="deleteProduct"
                    :disabled="form.processing"
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete Product
                </Button>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Name Field -->
                    <FormField name="name">
                        <FormItem>
                            <FormLabel>Name *</FormLabel>
                            <FormControl>
                                <Input
                                    v-model="form.name"
                                    placeholder="Enter product name"
                                    :disabled="form.processing"
                                />
                            </FormControl>
                            <FormMessage v-if="form.errors.name">
                                {{ form.errors.name }}
                            </FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- SKU Field (Read-only) -->
                    <FormField name="sku">
                        <FormItem>
                            <FormLabel>SKU</FormLabel>
                            <FormControl>
                                <Input
                                    v-model="form.sku"
                                    disabled
                                    class="font-mono"
                                />
                            </FormControl>
                            <p class="text-sm text-muted-foreground">
                                SKU cannot be changed after creation
                            </p>
                        </FormItem>
                    </FormField>

                    <!-- Price and Stock - Side by Side -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <FormField name="price">
                            <FormItem>
                                <FormLabel>Price *</FormLabel>
                                <FormControl>
                                    <Input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        :disabled="form.processing"
                                    />
                                </FormControl>
                                <FormMessage v-if="form.errors.price">
                                    {{ form.errors.price }}
                                </FormMessage>
                            </FormItem>
                        </FormField>

                        <FormField name="stock">
                            <FormItem>
                                <FormLabel>Stock *</FormLabel>
                                <FormControl>
                                    <Input
                                        v-model="form.stock"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        :disabled="form.processing"
                                    />
                                </FormControl>
                                <FormMessage v-if="form.errors.stock">
                                    {{ form.errors.stock }}
                                </FormMessage>
                            </FormItem>
                        </FormField>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="cancel"
                            :disabled="form.processing"
                        >
                            Reset
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.isDirty"
                        >
                            Save Changes
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
