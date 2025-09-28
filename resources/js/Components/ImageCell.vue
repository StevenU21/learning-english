<template>
    <div class="flex items-center justify-center w-full h-full">
        <img v-if="src" :src="src" :alt="alt || 'Imagen'"
            class="w-12 h-12 object-cover rounded shadow border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900"
            loading="lazy" />
        <span v-else class="text-gray-400 italic">Sin imagen</span>
    </div>
</template>

<script setup>
const props = defineProps({
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
});
</script>
