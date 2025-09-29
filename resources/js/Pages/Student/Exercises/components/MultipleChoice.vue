<script setup>
import { ref, watch } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean, lastAnswer: null });
const emit = defineEmits(['answered']);
const selected = ref(null);

function choose(opt) {
  if (props.showFeedback) return; // prevent changes after answer
  selected.value = opt;
  const correct = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(opt) : false;
  emit('answered', correct, opt);
}
</script>
<template>
  <div class="space-y-3">
    <button
      v-for="opt in props.exercise.options"
      :key="opt"
      @click="choose(opt)"
      :class="[
        'w-full text-left px-4 py-3 rounded-md border text-sm font-medium transition',
        selected === opt && props.showFeedback ? (Array.isArray(props.exercise.solution) && props.exercise.solution.includes(opt) ? 'bg-green-500 text-white border-green-500' : 'bg-red-500 text-white border-red-500') : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'
      ]"
    >
      {{ opt }}
    </button>
  </div>
</template>
