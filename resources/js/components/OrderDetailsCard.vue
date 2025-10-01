<script lang="ts" setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Order } from '@/types/orders';
import ClientSelector from './ClientSelector.vue';

const { order } = defineProps<{
    order: Order;
}>();

const emit = defineEmits<{
    'update:client-id': [value: number | null];
}>();

const formatSearchPlaceholder = () => {
    if (order.client_id) {
        return (
            order.client.name ??
            order.client.contact_email ??
            order.client.contact_phone ??
            `Client #${order.client_id}`
        );
    }

    if (order.supplier_id) {
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

const updateClientId = (value: number | null) => {
    emit('update:client-id', value);
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

                <FormField v-slot="{ componentField }" name="client_id">
                    <FormItem>
                        <FormLabel>
                            {{ order.client_id ? 'Client' : 'Supplier' }}
                        </FormLabel>
                        <FormControl>
                            <ClientSelector
                                :defaultValue="formatSearchPlaceholder()"
                                :model-value="getCurrentClientId()"
                                :name="componentField.name"
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
                    <p class="text-base">{{ order.quantity }}</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
