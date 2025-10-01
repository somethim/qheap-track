<script lang="ts" setup>
import { Client } from '@/types/orders';

defineProps<{ clients: Client[] }>();
</script>

<template>
    <div>
        {{ clients }}
    </div>
</template>
