<script setup>
import { ref } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);

const selected = ref([]);

function toggle(option) {
    if (props.showFeedback) return;
    const idx = selected.value.indexOf(option);
    if (idx >= 0) selected.value.splice(idx, 1); else selected.value.push(option);
}

function submit() {
    if (props.showFeedback) return;
    const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const correct = JSON.stringify([...selected.value].sort()) === JSON.stringify([...target].sort());
    emit('answered', correct, selected.value);
}
</script>
<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-2">
            <button v-for="opt in props.exercise.options" :key="opt" @click="toggle(opt)"
                :class="['px-3 py-1.5 rounded-full text-sm border', selected.includes(opt) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200']">
                {{ opt }}
            </button>
        </div>
        <button @click="submit" :disabled="showFeedback || selected.length === 0"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
