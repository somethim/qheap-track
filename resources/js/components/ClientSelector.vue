<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { search } from '@/routes/clients';
import { Client } from '@/types/orders';
import { Check, Search, X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: number | null;
    defaultValue?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const emitUpdate = (value: number | null | undefined) => {
    emit('update:modelValue', value ?? null);
};

const clientsList = ref<Client[]>([]);
const selectedClient = ref<Client | null>(null);
const searchTerm = ref(props.defaultValue ?? '');
const showSuggestions = ref(false);
const searchTimeout = ref<number | null>(null);

const performSearch = async (term: string) => {
    const queryTerm = term.trim() === '' ? undefined : term.trim();
    const url = search.url({ query: { term: queryTerm } });
    try {
        const response = await fetch(url);
        if (!response.ok) {
            console.error('Error fetching clients:', response.statusText);
            return;
        }
        clientsList.value = await response.json();
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
};

const clearSearchTimeout = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
        searchTimeout.value = null;
    }
};

const selectClient = (client: Client) => {
    selectedClient.value = client;
    emitUpdate(client.id);
    searchTerm.value =
        client.name ??
        client.contact_email ??
        client.contact_phone ??
        `Client #${client.id}`;
    showSuggestions.value = false;
};

const clearSelection = () => {
    selectedClient.value = null;
    emitUpdate(null);
    searchTerm.value = props.defaultValue ?? '';
    showSuggestions.value = false;
};

const handleInputFocus = () => {
    showSuggestions.value = true;
};

const handleInputBlur = () => {
    showSuggestions.value = false;
};

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue && selectedClient.value?.id !== newValue) {
            const client = clientsList.value.find((c) => c.id === newValue);
            if (client) {
                selectedClient.value = client;
                searchTerm.value =
                    client.name ??
                    client.contact_email ??
                    client.contact_phone ??
                    `Client #${client.id}`;
            }
        } else if (!newValue) {
            selectedClient.value = null;
            searchTerm.value = props.defaultValue ?? '';
        }
    },
);

watch(searchTerm, (newTerm) => {
    clearSearchTimeout();
    searchTimeout.value = window.setTimeout(() => {
        performSearch(newTerm);
    }, 200);
});

onMounted(() => {
    performSearch('');
});

onBeforeUnmount(() => {
    clearSearchTimeout();
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
                :placeholder="props.defaultValue || 'Search for a client...'"
                class="w-full pr-10 pl-10"
                @blur="handleInputBlur"
                @focus="handleInputFocus"
            />
            <Button
                v-if="selectedClient"
                class="absolute top-1/2 right-1 h-6 w-6 -translate-y-1/2 p-0"
                size="sm"
                variant="ghost"
                @click="clearSelection"
            >
                <X class="h-3 w-3" />
            </Button>
        </div>

        <div
            v-if="showSuggestions && clientsList.length > 0"
            class="absolute top-full z-50 mt-1 w-full rounded-md border bg-background shadow-lg"
        >
            <div class="max-h-60 overflow-y-auto">
                <div
                    v-for="client in clientsList"
                    :key="client.id"
                    class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm hover:bg-accent"
                    @mousedown.prevent="selectClient(client)"
                >
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">
                            {{ client.name }}
                        </div>
                        <div class="space-y-1 text-xs text-muted-foreground">
                            <div v-if="client.contact_email" class="truncate">
                                {{ client.contact_email }}
                            </div>
                            <div v-if="client.contact_phone" class="truncate">
                                {{ client.contact_phone }}
                            </div>
                        </div>
                    </div>
                    <Check
                        v-if="selectedClient?.id === client.id"
                        class="h-4 w-4 text-primary"
                    />
                </div>
            </div>
        </div>

        <div
            v-if="
                showSuggestions && clientsList.length === 0 && searchTerm.trim()
            "
            class="absolute top-full z-50 mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm text-muted-foreground shadow-lg"
        >
            No clients found
        </div>
    </div>
</template>
