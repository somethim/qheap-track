<script lang="ts" setup>
import SearchSelector from '@/components/SearchSelector.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { search as clientSearch } from '@/routes/clients';
import { search as supplierSearch } from '@/routes/suppliers';
import { Order } from '@/types/orders';

const { order } = defineProps<{
    order: Order;
}>();

const emit = defineEmits<{
    'update:client-id': [value: number | null];
}>();

const formatSearchPlaceholder = () => {
    if (order.client_id && order.client) {
        return (
            order.client.name ??
            order.client.contact_email ??
            order.client.contact_phone ??
            `Client #${order.client_id}`
        );
    }

    if (order.supplier_id && order.supplier) {
        return (
            order.supplier.name ??
            order.supplier.contact_email ??
            order.supplier.contact_phone ??
            `Supplier #${order.supplier_id}`
        );
    }

    return undefined;
};

const getCurrentClientId = () => {
    return order.client_id ? order.client_id : order.supplier_id;
};

const isClientOrder = () => {
    return Boolean(order.client_id);
};

const getSearchUrl = () => {
    return isClientOrder() ? clientSearch().url : supplierSearch().url;
};

const getSearchPlaceholder = () => {
    return isClientOrder()
        ? 'Search for a client...'
        : 'Search for a supplier...';
};

const getNoResultsText = () => {
    return isClientOrder() ? 'No clients found' : 'No suppliers found';
};

const updateClientId = (value: string | number | null) => {
    emit('update:client-id', value as number | null);
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Order Details</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-6">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Order Number
                    </p>
                    <p class="text-base">
                        {{ order.order_number }}
                    </p>
                </div>

                <FormField
                    :name="isClientOrder() ? 'client_id' : 'supplier_id'"
                >
                    <FormItem>
                        <FormLabel>
                            {{ isClientOrder() ? 'Client' : 'Supplier' }}
                        </FormLabel>
                        <FormControl>
                            <SearchSelector
                                :default-value="formatSearchPlaceholder()"
                                :display-field="'name'"
                                :id-field="'id'"
                                :model-value="getCurrentClientId()"
                                :no-results-text="getNoResultsText()"
                                :placeholder="getSearchPlaceholder()"
                                :response-key="
                                    isClientOrder() ? 'clients' : 'suppliers'
                                "
                                :search-param="'term'"
                                :secondary-fields="[
                                    'contact_email',
                                    'contact_phone',
                                ]"
                                :url="getSearchUrl()"
                                @update:model-value="updateClientId"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Amount
                    </p>
                    <p class="text-base">
                        ${{ Number(order.cost).toFixed(2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Item Count
                    </p>
                    <p class="text-base">{{ order.stock }}</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
