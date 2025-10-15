<template>
  <div class="relative w-full max-w-xs">
    <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
      <div
        class="h-full transition-all duration-300"
        :class="barClass"
        :style="{ width: pct + '%' }"
      ></div>
    </div>
    <div class="absolute inset-0 flex items-center justify-center">
      <span class="text-[10px] sm:text-xs font-semibold text-gray-900 dark:text-gray-100">
        {{ pct }}%
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: { type: [Number, String], required: true }
});

const pct = computed(() => {
  const num = Number(props.value) || 0;
  return Math.min(Math.max(Math.round(num), 0), 100);
});

const barClass = computed(() => {
  if (pct.value >= 100) return 'bg-green-600';
  if (pct.value >= 67) return 'bg-green-500';
  if (pct.value >= 34) return 'bg-yellow-500';
  return 'bg-red-500';
});
</script>
