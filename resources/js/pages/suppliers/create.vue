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
import { PhoneField } from '@/components/ui/phone-field';
import AppLayout from '@/layouts/AppLayout.vue';
import suppliers from '@/routes/suppliers/index';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Suppliers',
        href: suppliers.index().url,
    },
    {
        title: 'Create Supplier',
        href: '',
    },
];

const form = useForm({
    name: '',
    description: '',
    contact_email: '',
    contact_phone: '',
    address: '',
});

const submitForm = () => {
    form.post(suppliers.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const cancel = () => {
    router.visit(suppliers.index().url);
};
</script>

<template>
    <Head title="Create Supplier" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Create New Supplier</CardTitle>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <FormMessage v-if="form.errors.contact_email">
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
                                <FormMessage v-if="form.errors.contact_phone">
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
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                        >
                            Create Supplier
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
