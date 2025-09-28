<template>
  <select
    v-bind="$attrs"
    :value="modelValue"
    @change="$emit('update:modelValue', ($event.target as HTMLSelectElement)?.value)"
    :class="[
      'block w-full mt-1 p-2 border rounded bg-white dark:bg-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500',
      $attrs.class || ''
    ]"
  >
    <slot />
  </select>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
const props = defineProps({
  modelValue: [String, Number, null],
});
</script>
