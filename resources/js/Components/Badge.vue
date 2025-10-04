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
