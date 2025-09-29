<script setup>
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);

function answer(val) {
  if (props.showFeedback) return;
  const correct = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(val);
  emit('answered', correct, val);
}
</script>
<template>
  <div class="grid grid-cols-2 gap-4">
    <button @click="answer('True')" class="px-4 py-3 rounded-md text-sm font-semibold bg-green-500 text-white hover:bg-green-600">Verdadero</button>
    <button @click="answer('False')" class="px-4 py-3 rounded-md text-sm font-semibold bg-red-500 text-white hover:bg-red-600">Falso</button>
  </div>
</template>
