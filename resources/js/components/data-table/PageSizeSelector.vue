<script lang="ts" setup>
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AcceptableValue } from 'reka-ui';

const { pageSize } = defineProps<{ pageSize: number }>();

const emit = defineEmits<{
    'page-size-change': [pageSize: number];
}>();

const handlePageSizeChange = (value: AcceptableValue) => {
    emit('page-size-change', Number(value));
};

const pageSizeOptions = [10, 25, 50, 100] as const;
</script>

<template>
    <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">Rows per page</p>
        <Select
            :model-value="`${pageSize}`"
            @update:model-value="handlePageSizeChange"
        >
            <SelectTrigger class="h-8">
                <SelectValue :placeholder="`${pageSize}`" />
            </SelectTrigger>
            <SelectContent side="bottom">
                <SelectItem
                    v-for="pageSizeOption in pageSizeOptions"
                    :key="pageSizeOption"
                    :value="`${pageSizeOption}`"
                >
                    {{ pageSizeOption }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
