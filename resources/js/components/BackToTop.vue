<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { ChevronUp } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const isVisible = ref(false);

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

const toggleVisibility = () => {
    isVisible.value = window.pageYOffset > 300;
};

onMounted(() => {
    window.addEventListener('scroll', toggleVisibility);
});

onUnmounted(() => {
    window.removeEventListener('scroll', toggleVisibility);
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-75 translate-y-4"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-75 translate-y-4"
    >
        <Button
            v-if="isVisible"
            aria-label="Back to top"
            class="fixed right-6 bottom-6 z-50 h-12 w-12 rounded-full border-2 bg-background/80 shadow-lg backdrop-blur-sm transition-all duration-200 hover:scale-105 hover:shadow-xl"
            size="icon"
            variant="outline"
            @click="scrollToTop"
        >
            <ChevronUp class="h-5 w-5" />
        </Button>
    </Transition>
</template>
