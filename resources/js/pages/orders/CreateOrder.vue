<script lang="ts" setup>
import { Order } from '@/types/orders';

defineProps<Order>();
</script>
<template>
    <div>CreateOrder</div>
    <p v-for="(value, key) in $props" :key="key">{{ key }}: {{ value }}</p>
</template>
