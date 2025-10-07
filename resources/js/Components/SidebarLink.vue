<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean, default: false },
  method: { type: String, default: undefined },
  as: { type: String, default: undefined },
  compact: { type: Boolean, default: false },
  titleText: { type: String, default: '' },
});

const classes = computed(() => [
  'flex items-center rounded-md text-sm font-medium',
  props.compact ? 'justify-center p-2' : 'gap-2 px-3 py-2',
  'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
  'dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white',
  props.active ? 'bg-gray-200 text-gray-900 dark:bg-gray-800 dark:text-white' : ''
]);
</script>

<template>
  <Link :href="href" :method="method" :as="as" :class="classes" :title="titleText">
    <slot />
  </Link>
</template>
