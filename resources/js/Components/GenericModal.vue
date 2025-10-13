<script setup>
import { defineEmits, defineProps } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  maxWidthClass: { type: String, default: 'max-w-4xl' }
})

const emit = defineEmits(['close'])

function close() {
  emit('close')
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" @click="close" />

    <!-- Modal Container -->
    <div :class="['relative w-full mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 overflow-y-auto max-h-[80vh]', maxWidthClass]">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <slot name="header">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ title }}</h3>
        </slot>
        <button @click="close" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
          <i class="fa-solid fa-xmark text-xl"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <slot />
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
