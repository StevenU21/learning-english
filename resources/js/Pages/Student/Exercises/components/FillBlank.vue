<script setup>
import { ref } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);
const inputVal = ref('');

function submit() {
  if (props.showFeedback) return;
  const solution = Array.isArray(props.exercise.solution) ? props.exercise.solution.map(s=>String(s).trim().toLowerCase()) : [];
  const correct = solution.includes(inputVal.value.trim().toLowerCase());
  emit('answered', correct, inputVal.value);
}
</script>
<template>
  <div class="space-y-3">
    <input v-model="inputVal" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm" placeholder="Escribe tu respuesta" />
    <button @click="submit" class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Responder</button>
  </div>
</template>
