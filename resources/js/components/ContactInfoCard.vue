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
import { Input } from '@/components/ui/input';
import { PhoneField } from '@/components/ui/phone-field';
import { search as clientSearch } from '@/routes/clients/index';
import { search as supplierSearch } from '@/routes/suppliers/index';
import { Client, Order, Supplier } from '@/types/orders';

type ContactForm = {
    name: string;
    contact_email: string;
    contact_phone: string;
    address: string;
};

const props = defineProps<{
    contact: ContactForm;
    order: Order | null;
    orderType?: 'client' | 'supplier';
    selectedClientData?: Client | Supplier | null;
}>();

const emit = defineEmits<{
    'update:contact': [value: ContactForm];
    'update:client-id': [value: number | null, data?: Client | Supplier | null];
}>();

const updateContact = (field: keyof ContactForm, value: string | number) => {
    const updatedContact = { ...props.contact, [field]: String(value) };
    emit('update:contact', updatedContact);
};

const formatSearchPlaceholder = () => {
    if (props.selectedClientData?.name) return props.selectedClientData.name;
    if (!props.order) return undefined;

    if (props.order.client_id && 'client' in props.order) {
        return (
            props.order.client.name ??
            props.order.client.contact_email ??
            props.order.client.contact_phone ??
            `Client #${props.order.client_id}`
        );
    }

    if (props.order.supplier_id && 'supplier' in props.order) {
        return (
            props.order.supplier.name ??
            props.order.supplier.contact_email ??
            props.order.supplier.contact_phone ??
            `Supplier #${props.order.supplier_id}`
        );
    }

    return undefined;
};

const getCurrentClientId = () => {
    if (!props.order) return null;
    return props.order.client_id
        ? props.order.client_id
        : props.order.supplier_id;
};

const isClientOrder = () => {
    if (props.orderType) return props.orderType === 'client';
    return Boolean(props.order?.client_id);
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

const handleClientSelect = (item: Record<string, unknown> | null) => {
    const id = (item?.id as number | undefined) ?? null;
    const clientData = item as Client | Supplier | null;

    emit('update:client-id', id, clientData);

    if (clientData) {
        const updatedContact: ContactForm = {
            name: clientData.name || '',
            contact_email: clientData.contact_email || '',
            contact_phone: clientData.contact_phone || '',
            address: clientData.address || '',
        };
        emit('update:contact', updatedContact);
    }
};
</script>

<template>
    <Card>
        <CardHeader
            class="py-3"
            :class="
                $slots.headerAction
                    ? 'flex flex-row items-center justify-between'
                    : ''
            "
        >
            <CardTitle class="text-lg">Contact Information</CardTitle>
            <slot name="headerAction" />
        </CardHeader>
        <CardContent class="pb-4">
            <div class="space-y-4">
                <!-- Client/Supplier Selector -->
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
                                :search-param="'search'"
                                :secondary-fields="[
                                    'contact_email',
                                    'contact_phone',
                                ]"
                                :showIcons="true"
                                :url="getSearchUrl()"
                                @select="handleClientSelect"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Email and Phone - Side by Side -->
                <div class="grid grid-cols-2 gap-4">
                    <FormField name="contact_info.contact_email">
                        <FormItem>
                            <FormLabel>Email</FormLabel>
                            <FormControl>
                                <Input
                                    :model-value="props.contact.contact_email"
                                    placeholder="Enter email address"
                                    type="email"
                                    @update:model-value="
                                        (value) =>
                                            updateContact(
                                                'contact_email',
                                                value,
                                            )
                                    "
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField name="contact_info.contact_phone">
                        <FormItem>
                            <FormLabel>Phone</FormLabel>
                            <FormControl>
                                <PhoneField
                                    :model-value="props.contact.contact_phone"
                                    placeholder="Enter phone number"
                                    default-country-code="+355"
                                    @update:model-value="
                                        (value) =>
                                            updateContact(
                                                'contact_phone',
                                                value,
                                            )
                                    "
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <!-- Address - Full Width -->
                <FormField name="contact_info.address">
                    <FormItem>
                        <FormLabel>Address</FormLabel>
                        <FormControl>
                            <Input
                                :model-value="props.contact.address"
                                placeholder="Enter address"
                                @update:model-value="
                                    (value) => updateContact('address', value)
                                "
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
            </div>
        </CardContent>
    </Card>
</template>
