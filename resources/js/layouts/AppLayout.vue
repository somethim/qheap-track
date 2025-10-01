<script lang="ts" setup>
import BackToTop from '@/components/BackToTop.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-auto rounded-xl p-4"
        >
            <slot />
            <BackToTop />
        </div>
    </AppLayout>
</template>
