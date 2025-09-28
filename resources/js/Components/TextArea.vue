<template>
  <textarea
    v-bind="$attrs"
    :class="[
      'rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500',
      $attrs.class || ''
    ]"
  >
  </textarea>
</template>

<script setup lang="ts">
// Simple textarea component for consistent styling
</script>
