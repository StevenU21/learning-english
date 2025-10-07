<template>
    <select v-bind="$attrs" :value="modelValue"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement)?.value)" :class="[
            'block w-full mt-1 p-2 rounded-md border border-transparent bg-gray-800 text-white text-xs font-semibold uppercase tracking-widest transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300',
            $attrs.class || ''
        ]">
        <slot />
    </select>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
const props = defineProps({
    modelValue: [String, Number, null],
});
</script>
