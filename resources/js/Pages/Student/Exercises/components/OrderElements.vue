<script setup>
import { ref } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);
const currentOrder = ref([...(props.exercise.options || [])]);

function move(idx, dir) {
  if (props.showFeedback) return;
  const newIdx = idx + dir;
  if (newIdx < 0 || newIdx >= currentOrder.value.length) return;
  const temp = currentOrder.value[idx];
  currentOrder.value[idx] = currentOrder.value[newIdx];
  currentOrder.value[newIdx] = temp;
}

function submit() {
  if (props.showFeedback) return;
  const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
  const correct = JSON.stringify(target) === JSON.stringify(currentOrder.value);
  emit('answered', correct, currentOrder.value);
}
</script>
<template>
  <div class="space-y-3">
    <ul class="space-y-2">
      <li v-for="(item, idx) in currentOrder" :key="item" class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded text-sm text-gray-800 dark:text-gray-200">
        <span>{{ item }}</span>
        <div class="flex gap-2">
          <button @click="move(idx, -1)" class="px-2 py-1 rounded bg-white dark:bg-gray-600 shadow text-xs" :disabled="idx===0">↑</button>
          <button @click="move(idx, 1)" class="px-2 py-1 rounded bg-white dark:bg-gray-600 shadow text-xs" :disabled="idx===currentOrder.length-1">↓</button>
        </div>
      </li>
    </ul>
    <button @click="submit" class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Confirmar</button>
  </div>
</template>
