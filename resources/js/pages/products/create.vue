<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/products/index';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: products.index().url,
    },
    {
        title: 'Create Product',
        href: '',
    },
];

const form = useForm({
    name: '',
    price: '',
    stock: '',
});

const submitForm = () => {
    form.post(products.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const cancel = () => {
    router.visit(products.index().url);
};
</script>

<template>
    <Head title="Create Product" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Create New Product</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="space-y-6" @submit.prevent="submitForm">
                    <!-- Name Field -->
                    <FormField name="name">
                        <FormItem>
                            <FormLabel>Name *</FormLabel>
                            <FormControl>
                                <Input
                                    v-model="form.name"
                                    :disabled="form.processing"
                                    placeholder="Enter product name"
                                />
                            </FormControl>
                            <FormMessage v-if="form.errors.name">
                                {{ form.errors.name }}
                            </FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- SKU Info -->
                    <div class="rounded-lg border border-muted bg-muted/50 p-4">
                        <p class="text-sm text-muted-foreground">
                            ℹ️ A unique SKU will be automatically generated for
                            this product
                        </p>
                    </div>

                    <!-- Price and Stock - Side by Side -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <FormField name="price">
                            <FormItem>
                                <FormLabel>Price *</FormLabel>
                                <FormControl>
                                    <Input
                                        v-model="form.price"
                                        :disabled="form.processing"
                                        min="0"
                                        placeholder="0.00"
                                        step="0.01"
                                        type="number"
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
                                        :disabled="form.processing"
                                        min="0"
                                        placeholder="0"
                                        type="number"
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
                            :disabled="form.processing"
                            type="button"
                            variant="outline"
                            @click="cancel"
                        >
                            Cancel
                        </Button>
                        <Button :disabled="form.processing" type="submit">
                            Create Product
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
