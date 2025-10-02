<script generic="TItem extends Record<string, unknown>" lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useApiSearch } from '@/composables/useApiSearch';
import { Check, Search, X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface SearchSelectorProps<TItem extends Record<string, unknown>> {
    url: string;
    modelValue: number | string | null;
    defaultValue?: string;
    placeholder?: string;
    displayField: keyof TItem;
    secondaryFields?: (keyof TItem)[];
    idField: keyof TItem;
    noResultsText?: string;
    searchDelay?: number;
    responseKey?: string;
    searchParam?: string;
}

const props = withDefaults(defineProps<SearchSelectorProps<TItem>>(), {
    placeholder: 'Search...',
    secondaryFields: () => [],
    noResultsText: 'No results found',
    searchDelay: 300,
    responseKey: 'searchResults',
    searchParam: 'term',
});

const emit = defineEmits<{
    'update:modelValue': [value: number | string | null];
}>();

const emitUpdate = (value: number | string | null | undefined) => {
    emit('update:modelValue', value ?? null);
};

const selectedItem = ref<TItem | null>(null);
const searchTerm = ref(props.defaultValue ?? '');
const showSuggestions = ref(false);

const { itemsList, performSearch, clearResults } = useApiSearch<TItem>(
    props.url,
    props.searchDelay,
    props.responseKey,
    props.searchParam,
);

const getDisplayValue = (item: TItem): string => {
    const displayValue = item[props.displayField];
    if (displayValue) {
        return String(displayValue);
    }

    for (const field of props.secondaryFields) {
        const value = item[field];
        if (value) {
            return String(value);
        }
    }

    const idValue = item[props.idField];
    return `Item #${idValue}`;
};

const getSecondaryFieldValue = (
    item: TItem,
    fieldName: keyof TItem,
): string => {
    const value = item[fieldName];
    return value ? String(value) : '';
};

const hasSecondaryFieldValue = (
    item: TItem,
    fieldName: keyof TItem,
): boolean => {
    const value = item[fieldName];
    return Boolean(value);
};

const selectItem = (item: TItem) => {
    selectedItem.value = item;
    emitUpdate(item[props.idField] as number | string);
    searchTerm.value = getDisplayValue(item);
    showSuggestions.value = false;
};

const clearSelection = () => {
    selectedItem.value = null;
    emitUpdate(null);
    searchTerm.value = props.defaultValue ?? '';
    showSuggestions.value = false;
};

const handleInputFocus = () => {
    showSuggestions.value = true;
};

const handleInputBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
};

watch(
    () => props.modelValue,
    (newValue) => {
        if (
            newValue &&
            (selectedItem.value as TItem)?.[props.idField] !== newValue
        ) {
            const item = itemsList.value.find(
                (item) => (item as TItem)[props.idField] === newValue,
            );
            if (item) {
                selectedItem.value = item as TItem;
                const newDisplayValue = getDisplayValue(item as TItem);
                if (searchTerm.value !== newDisplayValue) {
                    searchTerm.value = newDisplayValue;
                }
            }
        } else if (!newValue) {
            selectedItem.value = null;
            const newDefaultValue = props.defaultValue ?? '';
            if (searchTerm.value !== newDefaultValue) {
                searchTerm.value = newDefaultValue;
            }
        }
    },
);

watch(searchTerm, (newTerm) => {
    performSearch(newTerm);
});

onMounted(() => {
    if (!props.defaultValue) {
        performSearch('');
    }
});

onBeforeUnmount(() => {
    clearResults();
});
</script>

<template>
    <div class="relative w-full">
        <div class="relative">
            <Search
                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="searchTerm"
                :placeholder="props.placeholder"
                class="w-full pr-10 pl-10"
                @blur="handleInputBlur"
                @focus="handleInputFocus"
            />
            <Button
                v-if="selectedItem"
                class="absolute top-1/2 right-1 h-6 w-6 -translate-y-1/2 p-0"
                size="sm"
                variant="ghost"
                @click="clearSelection"
            >
                <X class="h-3 w-3" />
            </Button>
        </div>

        <div
            v-if="showSuggestions && itemsList.length > 0"
            class="absolute top-full z-50 mt-1 w-full rounded-md border bg-background shadow-lg"
        >
            <div class="max-h-60 overflow-y-auto">
                <div
                    v-for="item in itemsList"
                    :key="String((item as TItem)[props.idField])"
                    class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm hover:bg-accent"
                    @mousedown.prevent="selectItem(item as TItem)"
                >
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">
                            {{ getDisplayValue(item as TItem) }}
                        </div>
                        <div
                            v-if="props.secondaryFields.length > 0"
                            class="space-y-1 text-xs text-muted-foreground"
                        >
                            <template
                                v-for="fieldName in props.secondaryFields"
                                :key="String(fieldName)"
                            >
                                <div
                                    v-if="
                                        hasSecondaryFieldValue(
                                            item as TItem,
                                            fieldName,
                                        )
                                    "
                                    class="truncate"
                                >
                                    {{
                                        getSecondaryFieldValue(
                                            item as TItem,
                                            fieldName,
                                        )
                                    }}
                                </div>
                            </template>
                        </div>
                    </div>
                    <Check
                        v-if="
                            (selectedItem as TItem)?.[props.idField] ===
                            (item as TItem)[props.idField]
                        "
                        class="h-4 w-4 text-primary"
                    />
                </div>
            </div>
        </div>

        <div
            v-if="
                showSuggestions && itemsList.length === 0 && searchTerm.trim()
            "
            class="absolute top-full z-50 mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm text-muted-foreground shadow-lg"
        >
            {{ props.noResultsText }}
        </div>
    </div>
</template>
