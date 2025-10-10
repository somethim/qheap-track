<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { PhoneField } from '@/components/ui/phone-field';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import orders from '@/routes/orders/index';
import suppliers from '@/routes/suppliers/index';
import { BreadcrumbItem } from '@/types';
import { Supplier } from '@/types/orders';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { watch } from 'vue';

const props = defineProps<{ supplier: Supplier }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Suppliers',
        href: suppliers.index().url,
    },
    {
        title: props.supplier.name,
        href: '',
    },
];

const form = useForm({
    name: props.supplier.name,
    description: props.supplier.description || '',
    contact_email: props.supplier.contact_email || '',
    contact_phone: props.supplier.contact_phone || '',
    address: props.supplier.address || '',
});

watch(
    () => props.supplier,
    (newSupplier) => {
        form.defaults({
            name: newSupplier.name,
            description: newSupplier.description || '',
            contact_email: newSupplier.contact_email || '',
            contact_phone: newSupplier.contact_phone || '',
            address: newSupplier.address || '',
        });
        form.reset();
    },
);

const submitForm = () => {
    form.put(suppliers.update(props.supplier.id).url, {
        preserveScroll: true,
    });
};

const deleteSupplier = () => {
    if (
        confirm(
            'Are you sure you want to delete this supplier? This action cannot be undone.',
        )
    ) {
        const route = suppliers.destroy(props.supplier);
        router.visit(route.url, { method: route.method });
    }
};

const cancel = () => {
    form.reset();
};

const viewOrder = (orderId: number) => {
    router.visit(orders.show(orderId).url);
};
</script>

<template>
    <Head :title="`Supplier: ${supplier.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4">
            <!-- Supplier Details Card -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between py-3"
                >
                    <CardTitle class="text-lg">Supplier Information</CardTitle>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="deleteSupplier"
                        :disabled="form.processing"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Supplier
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
                                        placeholder="Enter supplier name"
                                        :disabled="form.processing"
                                    />
                                </FormControl>
                                <FormMessage v-if="form.errors.name">
                                    {{ form.errors.name }}
                                </FormMessage>
                            </FormItem>
                        </FormField>

                        <!-- Description Field -->
                        <FormField name="description">
                            <FormItem>
                                <FormLabel>Description</FormLabel>
                                <FormControl>
                                    <Input
                                        v-model="form.description"
                                        placeholder="Enter supplier description"
                                        :disabled="form.processing"
                                    />
                                </FormControl>
                                <FormMessage v-if="form.errors.description">
                                    {{ form.errors.description }}
                                </FormMessage>
                            </FormItem>
                        </FormField>

                        <!-- Email and Phone - Side by Side -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <FormField name="contact_email">
                                <FormItem>
                                    <FormLabel>Email</FormLabel>
                                    <FormControl>
                                        <Input
                                            v-model="form.contact_email"
                                            type="email"
                                            placeholder="Enter email address"
                                            :disabled="form.processing"
                                        />
                                    </FormControl>
                                    <FormMessage
                                        v-if="form.errors.contact_email"
                                    >
                                        {{ form.errors.contact_email }}
                                    </FormMessage>
                                </FormItem>
                            </FormField>

                            <FormField name="contact_phone">
                                <FormItem>
                                    <FormLabel>Phone</FormLabel>
                                    <FormControl>
                                        <PhoneField
                                            v-model="form.contact_phone"
                                            placeholder="Enter phone number"
                                            default-country-code="+355"
                                            :disabled="form.processing"
                                        />
                                    </FormControl>
                                    <FormMessage
                                        v-if="form.errors.contact_phone"
                                    >
                                        {{ form.errors.contact_phone }}
                                    </FormMessage>
                                </FormItem>
                            </FormField>
                        </div>

                        <!-- Address Field -->
                        <FormField name="address">
                            <FormItem>
                                <FormLabel>Address</FormLabel>
                                <FormControl>
                                    <Input
                                        v-model="form.address"
                                        placeholder="Enter address"
                                        :disabled="form.processing"
                                    />
                                </FormControl>
                                <FormMessage v-if="form.errors.address">
                                    {{ form.errors.address }}
                                </FormMessage>
                            </FormItem>
                        </FormField>

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

            <!-- Orders List Card -->
            <Card v-if="supplier.orders && supplier.orders.length > 0">
                <CardHeader
                    class="flex flex-row items-center justify-between py-3"
                >
                    <CardTitle class="text-lg"
                        >Orders ({{ supplier.orders.length }})</CardTitle
                    >
                    <div class="text-lg font-semibold">
                        Total: {{ supplier.formatted_cost }}
                    </div>
                </CardHeader>
                <CardContent class="pb-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Order Number</TableHead>
                                <TableHead>Items</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="order in supplier.orders"
                                :key="order.id"
                                class="cursor-pointer hover:bg-muted/50"
                                @click="viewOrder(order.id)"
                            >
                                <TableCell class="font-mono">
                                    {{ order.order_number.slice(-8) }}
                                </TableCell>
                                <TableCell>{{ order.stock }}</TableCell>
                                <TableCell class="font-mono">
                                    {{ formatCurrency(order.cost) }}
                                </TableCell>
                                <TableCell class="font-mono">
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- No Orders Message -->
            <Card v-else>
                <CardContent class="py-8 text-center text-muted-foreground">
                    No orders found for this supplier.
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
