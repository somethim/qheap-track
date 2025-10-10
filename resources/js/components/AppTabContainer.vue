<script lang="ts" setup>
import { useTabs } from '@/composables/useTabs';
import { computed, onMounted } from 'vue';

const { tabs, activeTabId, initialize } = useTabs();

const activeTab = computed(() =>
    tabs.value.find((tab) => tab.id === activeTabId.value),
);

onMounted(() => {
    initialize();
});
</script>

<template>
    <div class="flex h-full w-full flex-col">
        <slot v-if="activeTab" :tab="activeTab" />
    </div>
</template>
