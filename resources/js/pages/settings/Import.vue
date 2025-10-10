<script lang="ts" setup>
import importRoute from '@/routes/import';
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { CheckCircle2, Download, FileUp, Info } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useToast } from '@/composables/useToast';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Import data',
        href: importRoute.edit.url(),
    },
];

const { error } = useToast();

const isDragging = ref(false);

const form = useForm({
    resource: 'products' as string,
    file: null as File | null,
});

const resourceOptions = [
    {
        value: 'products',
        label: 'Products',
        description: 'Import products with SKU, price, and stock',
    },
    {
        value: 'clients',
        label: 'Clients',
        description: 'Import clients with contact details',
    },
    {
        value: 'suppliers',
        label: 'Suppliers',
        description: 'Import suppliers with contact details',
    },
];

const currentResource = computed(() =>
    resourceOptions.find((opt) => opt.value === form.resource),
);

const csvTemplates = {
    products: {
        headers: ['name', 'sku', 'price', 'stock'],
        example: ['Product Name', 'SKU-001', '99.99', '100'],
        description:
            'Required: name, sku, price. Optional: stock (defaults to 0)',
    },
    clients: {
        headers: [
            'name',
            'description',
            'contact_email',
            'contact_phone',
            'address',
        ],
        example: [
            'Client Name',
            'Client description',
            'client@example.com',
            '+355 123 456',
            'Tirana, Albania',
        ],
        description:
            'Required: name. Optional: description, contact_email, contact_phone, address',
    },
    suppliers: {
        headers: [
            'name',
            'description',
            'contact_email',
            'contact_phone',
            'address',
        ],
        example: [
            'Supplier Name',
            'Supplier description',
            'supplier@example.com',
            '+355 987 654',
            'Durres, Albania',
        ],
        description:
            'Required: name. Optional: description, contact_email, contact_phone, address',
    },
};

const currentTemplate = computed(
    () => csvTemplates[form.resource as keyof typeof csvTemplates],
);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

const validateFile = (file: File): boolean => {
    const validTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
    const validExtensions = ['.csv', '.txt'];
    const isValidType = validTypes.includes(file.type) || 
                       validExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
    const isValidSize = file.size <= 10 * 1024 * 1024; // 10MB
    
    if (!isValidType) {
        error('Please upload a CSV file (.csv or .txt)');
        return false;
    }
    
    if (!isValidSize) {
        error('File size must be less than 10MB');
        return false;
    }
    
    return true;
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        const file = files[0];
        if (validateFile(file)) {
            form.file = file;
            const fileInput = document.getElementById('file') as HTMLInputElement;
            if (fileInput) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
            }
        }
    }
};

const handlePaste = (event: ClipboardEvent) => {
    const items = event.clipboardData?.items;
    if (!items) return;
    
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        if (item.kind === 'file') {
            event.preventDefault();
            const file = item.getAsFile();
            if (file && validateFile(file)) {
                form.file = file;
                const fileInput = document.getElementById('file') as HTMLInputElement;
                if (fileInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                }
            }
            break;
        }
    }
};

const downloadTemplate = () => {
    const template = currentTemplate.value;
    const csv = [template.headers.join(','), template.example.join(',')].join(
        '\n',
    );

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${form.resource}_template.csv`;
    link.click();
    URL.revokeObjectURL(url);
};

const submitForm = () => {
    form.post(importRoute.store.url(), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            
            const fileInput = document.getElementById('file') as HTMLInputElement;
            if (fileInput) {
                fileInput.value = '';
            }
        },
    });
};

onMounted(() => {
    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handlePaste);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Import data" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    description="Import data from CSV files"
                    title="Import data"
                />

                <!-- Grid Layout for Form and Guide -->
                <div class="flex flex-col gap-16 lg:flex-row">
                    <!-- Import Form -->
                    <div class="max-w-2xl flex-shrink-0">
                        <form
                            class="space-y-8"
                            @submit.prevent="submitForm"
                        >
                            <!-- Resource Selection -->
                            <div class="grid gap-2">
                                <Label for="resource"
                                    >Select resource type</Label
                                >
                                <Select
                                    v-model="form.resource"
                                    name="resource"
                                >
                                    <SelectTrigger id="resource">
                                        <SelectValue
                                            placeholder="Choose what to import..."
                                        >
                                            {{ currentResource?.label }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in resourceOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{
                                                    option.label
                                                }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >{{
                                                        option.description
                                                    }}</span
                                                >
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.resource" />
                            </div>

                            <!-- File Upload with Drag & Drop -->
                            <div class="grid gap-2">
                                <Label for="file">CSV file</Label>
                                <div
                                    class="relative"
                                    @dragover="handleDragOver"
                                    @dragleave="handleDragLeave"
                                    @drop="handleDrop"
                                >
                                    <div
                                        :class="[
                                            'rounded-lg border-2 border-dashed p-6 transition-colors',
                                            isDragging
                                                ? 'border-primary bg-primary/5'
                                                : 'border-border hover:border-primary/50',
                                        ]"
                                    >
                                        <div class="flex flex-col items-center gap-2 text-center">
                                            <FileUp
                                                :class="[
                                                    'h-8 w-8 transition-colors',
                                                    isDragging
                                                        ? 'text-primary'
                                                        : 'text-muted-foreground',
                                                ]"
                                            />
                                            <div class="text-sm">
                                                <label
                                                    for="file"
                                                    class="cursor-pointer font-medium text-primary hover:underline"
                                                >
                                                    Click to upload
                                                </label>
                                                <span class="text-muted-foreground">
                                                    or drag and drop
                                                </span>
                                            </div>
                                            <p class="text-xs text-muted-foreground">
                                                CSV or TXT files up to 10MB
                                            </p>
                                            <p class="text-xs text-muted-foreground italic">
                                                You can also paste a file (Ctrl+V / Cmd+V)
                                            </p>
                                        </div>
                                        <Input
                                            id="file"
                                            type="file"
                                            name="file"
                                            accept=".csv,.txt"
                                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                            @change="handleFileChange"
                                        />
                                    </div>
                                </div>
                                <p
                                    v-if="form.file"
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <CheckCircle2 class="h-4 w-4 text-green-600" />
                                    <span>
                                        Selected: {{ form.file.name }} ({{
                                            (form.file.size / 1024).toFixed(2)
                                        }}
                                        KB)
                                    </span>
                                </p>
                                <InputError :message="form.errors.file" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="form.processing || !form.file"
                                    type="submit"
                                    class="gap-2"
                                >
                                    <FileUp class="h-4 w-4" />
                                    {{
                                        form.processing
                                            ? 'Importing...'
                                            : 'Import data'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- CSV Template Guide -->
                    <div class="w-3xl">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Info class="h-5 w-5" />
                                    CSV Format Guide
                                </CardTitle>
                                <CardDescription>
                                    {{ currentResource?.label }} template
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <p
                                        class="mb-2 text-sm text-muted-foreground"
                                    >
                                        {{ currentTemplate.description }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-2 text-sm font-medium">
                                        Required CSV headers:
                                    </p>
                                    <div
                                        class="rounded-md bg-muted p-3 font-mono text-xs"
                                    >
                                        {{ currentTemplate.headers.join(',') }}
                                    </div>
                                </div>

                                <div>
                                    <p class="mb-2 text-sm font-medium">
                                        Example row:
                                    </p>
                                    <div
                                        class="rounded-md bg-muted p-3 font-mono text-xs"
                                    >
                                        {{ currentTemplate.example.join(',') }}
                                    </div>
                                </div>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="gap-2"
                                    @click="downloadTemplate"
                                >
                                    <Download class="h-4 w-4" />
                                    Download template
                                </Button>

                                <Alert>
                                    <Info class="h-4 w-4" />
                                    <AlertTitle>Important notes</AlertTitle>
                                    <AlertDescription class="space-y-1 text-xs">
                                        <ul
                                            class="list-inside list-disc space-y-1"
                                        >
                                            <li>
                                                The first row must contain the
                                                column headers
                                            </li>
                                            <li>
                                                Headers are case-insensitive
                                            </li>
                                            <li>Empty rows will be skipped</li>
                                            <li>Maximum file size: 10MB</li>
                                            <li>
                                                If more than 10 rows fail
                                                validation, the import will stop
                                            </li>
                                            <li>
                                                All imports are transactional -
                                                if there's an error, nothing
                                                will be imported
                                            </li>
                                        </ul>
                                    </AlertDescription>
                                </Alert>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
