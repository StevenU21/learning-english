<template>
  <div :class="[
    'rounded-xl border border-gray-200 dark:border-white/10 bg-white/60 dark:bg-gray-900/60 backdrop-blur-xl p-5 flex items-start justify-between shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)]',
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
