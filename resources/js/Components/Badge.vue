<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    type: { type: String, default: 'default' }
});

const badgeClass = computed(() => {
    const base = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold';
    switch (props.type) {
        case 'success':
            return base + ' bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'error':
            return base + ' bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'info':
            return base + ' bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800';
        case 'basic':
            return base + ' bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'intermedio':
            return base + ' bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 ring-1 ring-yellow-200 dark:ring-yellow-800';
        case 'avanzado':
            return base + ' bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 ring-1 ring-purple-200 dark:ring-purple-800';
        default:
            return base + ' bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
});
</script>
<template>
    <span :class="badgeClass">
        <slot />
    </span>
</template>
