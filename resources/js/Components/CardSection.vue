<template>
  <div class="py-0">
    <div class="w-full px-4 sm:px-6 lg:px-8">
      <div class="bg-white/60 dark:bg-gray-900/60 backdrop-blur-xl overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)] sm:rounded-xl border border-gray-200 dark:border-white/10">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
// No props needed for now, purely presentational
</script>
