<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useTabs } from '@/composables/useTabs';
import { dashboard } from '@/routes';
import { router } from '@inertiajs/vue3';
import { Plus, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const {
    tabs,
    activeTabId,
    switchTab,
    removeTab,
    closeOtherTabs,
    closeTabsToRight,
    closeAllTabs,
} = useTabs();

const openDropdownTabId = ref<string | null>(null);

const handleTabChange = (tabId: string | number) => {
    switchTab(String(tabId));
};

const handleCloseTab = (tabId: string, event: Event) => {
    event.stopPropagation();
    removeTab(tabId);
};

const handleContextMenu = (tabId: string, event: MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();
    openDropdownTabId.value = tabId;
};

const handleMouseDown = (tabId: string, event: MouseEvent) => {
    if (event.button === 1) {
        event.preventDefault();
        const tab = tabs.value.find((t) => t.id === tabId);
        if (tab && tab.closable) {
            removeTab(tabId);
        }
    }
    if (event.button === 2) {
        event.preventDefault();
        event.stopPropagation();
    }
};

const handleKeyDown = (event: KeyboardEvent) => {
    if ((event.ctrlKey || event.metaKey) && event.key === 'w') {
        event.preventDefault();
        if (activeTabId.value) {
            const tab = tabs.value.find((t) => t.id === activeTabId.value);
            if (tab && tab.closable) {
                removeTab(activeTabId.value);
            }
        }
    }

    if (
        (event.ctrlKey || event.metaKey) &&
        event.key === 'Tab' &&
        !event.shiftKey
    ) {
        event.preventDefault();
        const currentIndex = tabs.value.findIndex(
            (t) => t.id === activeTabId.value,
        );
        if (currentIndex >= 0 && currentIndex < tabs.value.length - 1) {
            switchTab(tabs.value[currentIndex + 1].id);
        } else if (currentIndex === tabs.value.length - 1) {
            switchTab(tabs.value[0].id);
        }
    }

    if (
        (event.ctrlKey || event.metaKey) &&
        event.key === 'Tab' &&
        event.shiftKey
    ) {
        event.preventDefault();
        const currentIndex = tabs.value.findIndex(
            (t) => t.id === activeTabId.value,
        );
        if (currentIndex > 0) {
            switchTab(tabs.value[currentIndex - 1].id);
        } else if (currentIndex === 0) {
            switchTab(tabs.value[tabs.value.length - 1].id);
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const handleNewTab = () => {
    router.visit(dashboard());
};
</script>

<template>
    <div v-if="tabs.length > 0" class="border-b bg-background">
        <Tabs
            :model-value="activeTabId ?? undefined"
            class="w-full"
            @update:model-value="
                (value: string | number) => handleTabChange(value)
            "
        >
            <TabsList
                class="h-auto w-full flex-wrap justify-start rounded-none border-0 bg-transparent p-0"
            >
                <Button
                    aria-label="New Tab (Dashboard)"
                    class="h-10 w-10 shrink-0 rounded-none"
                    size="icon"
                    title="New Tab (Dashboard)"
                    variant="ghost"
                    @click="handleNewTab"
                >
                    <Plus class="h-4 w-4" />
                </Button>
                <DropdownMenu
                    v-for="tab in tabs"
                    :key="tab.id"
                    :open="openDropdownTabId === tab.id"
                    @update:open="
                        (open: boolean) => {
                            if (!open) openDropdownTabId = null;
                        }
                    "
                >
                    <DropdownMenuTrigger as-child>
                        <TabsTrigger
                            :value="tab.id"
                            class="group relative rounded-none border-b-2 border-transparent px-4 py-3 data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none"
                            @contextmenu="
                                (e: MouseEvent) => handleContextMenu(tab.id, e)
                            "
                            @mousedown="
                                (e: MouseEvent) => handleMouseDown(tab.id, e)
                            "
                        >
                            <span class="mr-2 max-w-[200px] truncate">
                                {{ tab.title }}
                            </span>
                            <button
                                v-if="tab.closable"
                                :aria-label="`Close ${tab.title}`"
                                class="ml-1 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                @click="(e: Event) => handleCloseTab(tab.id, e)"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </TabsTrigger>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start">
                        <DropdownMenuItem
                            v-if="tab.closable"
                            @select="removeTab(tab.id)"
                        >
                            Close
                        </DropdownMenuItem>
                        <DropdownMenuItem @select="closeOtherTabs(tab.id)">
                            Close Others
                        </DropdownMenuItem>
                        <DropdownMenuItem @select="closeTabsToRight(tab.id)">
                            Close to the Right
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @select="closeAllTabs">
                            Close All
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </TabsList>
        </Tabs>
    </div>
</template>
