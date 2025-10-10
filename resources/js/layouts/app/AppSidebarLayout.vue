<script lang="ts" setup>
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import AppTabBar from '@/components/AppTabBar.vue';
import AppTabContainer from '@/components/AppTabContainer.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent class="overflow-x-hidden" variant="sidebar">
            <AppTabBar />
            <AppTabContainer>
                <template #default>
                    <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                    <slot />
                </template>
            </AppTabContainer>
        </AppContent>
    </AppShell>
</template>
