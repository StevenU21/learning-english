<template>
  <span :class="['px-2 py-1 rounded text-xs font-medium', badgeClasses]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  correct: { type: Boolean, required: true }
});

const badgeClasses = computed(() =>
  props.correct
    ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300'
    : 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300'
);

const label = computed(() => (props.correct ? 'Sí' : 'No'));
</script>
