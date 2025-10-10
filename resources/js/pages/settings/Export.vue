<script lang="ts" setup>
import { edit, store } from '@/routes/export';
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { AlertCircle, Database, Download, Info } from 'lucide-vue-next';
import { ref } from 'vue';

interface ExportStatus {
    type: 'success' | 'error';
    message: string;
}

interface Stats {
    products: number;
    clients: number;
    suppliers: number;
    orders: number;
}

interface Props {
    exportStatus?: ExportStatus;
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Export data',
        href: edit().url,
    },
];

const selectedResource = ref<string>('products');
const includeHeaders = ref<boolean>(true);
const isExporting = ref<boolean>(false);

const handleExport = () => {
    isExporting.value = true;

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.name = 'export-frame';
    document.body.appendChild(iframe);

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = store().url;
    form.target = 'export-frame';
    form.style.display = 'none';

    const csrf = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
    if (csrf) {
        const csrfInput = document.createElement('input');
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        form.appendChild(csrfInput);
    }

    const resourceInput = document.createElement('input');
    resourceInput.name = 'resource';
    resourceInput.value = selectedResource.value;
    form.appendChild(resourceInput);

    const headersInput = document.createElement('input');
    headersInput.name = 'include_headers';
    headersInput.value = includeHeaders.value ? '1' : '0';
    form.appendChild(headersInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    const loadingTimeout = setTimeout(() => {
        isExporting.value = false;
    }, 500);

    const fallbackTimeout = setTimeout(() => {
        isExporting.value = false;
        if (document.body.contains(iframe)) {
            document.body.removeChild(iframe);
        }
    }, 30000);

    iframe.addEventListener('load', () => {
        clearTimeout(loadingTimeout);
        clearTimeout(fallbackTimeout);
        isExporting.value = false;
        setTimeout(() => {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
            }
        }, 1000);
    }, { once: true });
};

const resourceOptions = [
    {
        value: 'products',
        label: 'Products',
        description: 'Export all products with pricing and stock',
        count: props.stats.products,
    },
    {
        value: 'clients',
        label: 'Clients',
        description: 'Export all clients with contact information',
        count: props.stats.clients,
    },
    {
        value: 'suppliers',
        label: 'Suppliers',
        description: 'Export all suppliers with contact information',
        count: props.stats.suppliers,
    },
];

const getResourceInfo = (value: string) => {
    return resourceOptions.find((opt) => opt.value === value);
};

const exportFields = {
    products: [
        'id',
        'name',
        'sku',
        'price',
        'stock',
        'created_at',
        'updated_at',
    ],
    clients: [
        'id',
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'address',
        'created_at',
        'updated_at',
    ],
    suppliers: [
        'id',
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'address',
        'created_at',
        'updated_at',
    ],
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Export data" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    description="Export your data to CSV files"
                    title="Export data"
                />

                <!-- Status Alert -->
                <Alert
                    v-if="props.exportStatus"
                    class="border-red-500/50 bg-red-50 dark:bg-red-950/20"
                >
                    <AlertCircle
                        class="h-4 w-4 text-red-600 dark:text-red-400"
                    />
                    <AlertTitle class="text-red-900 dark:text-red-100">
                        Error
                    </AlertTitle>
                    <AlertDescription class="text-red-800 dark:text-red-200">
                        {{ props.exportStatus.message }}
                    </AlertDescription>
                </Alert>

                <!-- Grid Layout for Form and Info -->
                <div class="flex flex-col gap-16 lg:flex-row">
                    <!-- Export Form -->
                    <div class="max-w-2xl flex-shrink-0">
                        <div class="space-y-8">
                            <!-- Resource Selection -->
                            <div class="grid gap-2">
                                <Label for="resource"
                                    >Select resource type</Label
                                >
                                <Select v-model="selectedResource">
                                    <SelectTrigger id="resource">
                                        <SelectValue
                                            placeholder="Choose what to export..."
                                        >
                                            {{
                                                getResourceInfo(
                                                    selectedResource,
                                                )?.label
                                            }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in resourceOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-4"
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
                                                <span
                                                    class="rounded-full bg-muted px-2 py-0.5 text-xs font-medium"
                                                >
                                                    {{ option.count }}
                                                </span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Options -->
                            <div class="grid gap-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="include_headers"
                                        v-model:checked="includeHeaders"
                                    />
                                    <Label
                                        class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        for="include_headers"
                                    >
                                        Include column headers
                                    </Label>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="
                                        isExporting ||
                                        getResourceInfo(selectedResource)
                                            ?.count === 0
                                    "
                                    type="button"
                                    class="gap-2"
                                    @click="handleExport"
                                >
                                    <Download class="h-4 w-4" />
                                    {{
                                        isExporting
                                            ? 'Exporting...'
                                            : 'Export to CSV'
                                    }}
                                </Button>

                                <p
                                    v-if="
                                        getResourceInfo(selectedResource)
                                            ?.count === 0
                                    "
                                    class="text-sm text-muted-foreground"
                                >
                                    No records to export
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Export Info Card -->
                    <div class="w-3xl">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Database class="h-5 w-5" />
                                    Export Information
                                </CardTitle>
                                <CardDescription>
                                    {{
                                        getResourceInfo(selectedResource)?.label
                                    }}
                                    export details
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2 rounded-lg border p-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-sm font-medium"
                                            >Total records:</span
                                        >
                                        <span class="text-sm font-semibold">
                                            {{
                                                getResourceInfo(
                                                    selectedResource,
                                                )?.count || 0
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-sm font-medium"
                                            >File format:</span
                                        >
                                        <span class="text-sm"
                                            >CSV (Comma-separated values)</span
                                        >
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-sm font-medium"
                                            >Encoding:</span
                                        >
                                        <span class="text-sm">UTF-8</span>
                                    </div>
                                </div>

                                <div>
                                    <p class="mb-2 text-sm font-medium">
                                        Exported fields:
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="field in exportFields[
                                                selectedResource as keyof typeof exportFields
                                            ]"
                                            :key="field"
                                            class="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-medium"
                                        >
                                            {{ field }}
                                        </span>
                                    </div>
                                </div>

                                <Alert>
                                    <Info class="h-4 w-4" />
                                    <AlertTitle>Export notes</AlertTitle>
                                    <AlertDescription class="space-y-1 text-xs">
                                        <ul
                                            class="list-inside list-disc space-y-1"
                                        >
                                            <li>
                                                The export includes all your
                                                {{ selectedResource }} data
                                            </li>
                                            <li>
                                                Timestamps are in Y-m-d H:i:s
                                                format
                                            </li>
                                            <li>
                                                Empty fields will be exported as
                                                empty strings
                                            </li>
                                            <li>
                                                Special characters are properly
                                                escaped
                                            </li>
                                            <li>
                                                The file will download
                                                automatically
                                            </li>
                                            <li>
                                                Only your own data is exported
                                                (user-scoped)
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
