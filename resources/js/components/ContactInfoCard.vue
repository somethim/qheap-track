<script lang="ts" setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';

type ContactForm = {
    contact_email: string;
    contact_phone: string;
    address: string;
};

const props = defineProps<{ contact: ContactForm }>();

const emit = defineEmits<{
    'update:contact': [value: ContactForm];
}>();

const updateContact = (field: keyof ContactForm, value: string | number) => {
    const updatedContact = { ...props.contact, [field]: String(value) };
    emit('update:contact', updatedContact);
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Contact Information</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-6">
                <FormField v-slot="{ componentField }" name="contact_email">
                    <FormItem>
                        <FormLabel>Email</FormLabel>
                        <FormControl>
                            <Input
                                :default-value="props.contact.contact_email"
                                :model-value="props.contact.contact_email"
                                placeholder="Enter email address"
                                type="email"
                                v-bind="componentField"
                                @update:model-value="
                                    (value) =>
                                        updateContact('contact_email', value)
                                "
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="contact_phone">
                    <FormItem>
                        <FormLabel>Phone</FormLabel>
                        <FormControl>
                            <Input
                                :default-value="props.contact.contact_phone"
                                :model-value="props.contact.contact_phone"
                                placeholder="Enter phone number"
                                type="tel"
                                v-bind="componentField"
                                @update:model-value="
                                    (value) =>
                                        updateContact('contact_phone', value)
                                "
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="address">
                    <FormItem>
                        <FormLabel>Address</FormLabel>
                        <FormControl>
                            <Input
                                :default-value="props.contact.address"
                                :model-value="props.contact.address"
                                placeholder="Enter address"
                                v-bind="componentField"
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
