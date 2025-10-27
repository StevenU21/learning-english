<template>
  <div :class="[
    'rounded-lg border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-800/70 p-4 flex items-start justify-between',
    customClass
  ]">
    <div class="flex flex-col">
      <p class="text-[11px] font-medium tracking-wide text-gray-500 dark:text-gray-400">{{ label }}</p>
      <p class="mt-2 text-2xl font-semibold text-gray-700 dark:text-gray-100 whitespace-nowrap">{{ value }}</p>
    </div>
    <div :class="['p-2 rounded-lg bg-gradient-to-br', gradient, iconColor]">
      <i :class="['fa-solid', icon]"></i>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, required: true },
  gradient: { type: String, default: '' },
  iconColor: { type: String, default: '' },
  customClass: { type: String, default: '' }
});
</script>
