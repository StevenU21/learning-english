<script setup>
import { ref } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);

const selected = ref([]);

function toggle(option) {
    if (props.showFeedback) return;
    if (selected.value.includes(option)) {
        selected.value = [];
    } else {
        selected.value = [option];
    }
}

function submit() {
    if (props.showFeedback) return;
    const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const correct = JSON.stringify([...selected.value].sort()) === JSON.stringify([...target].sort());
    emit('answered', correct, selected.value);
}

function getButtonClass(opt) {
    if (props.showFeedback && selected.value.includes(opt)) {
        const isCorrect = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(opt) : false;
        return isCorrect
            ? 'bg-gray-800 border-green-500 text-green-400'
            : 'bg-gray-800 border-red-500 text-red-400';
    }
    if (selected.value.includes(opt)) {
        return 'bg-gray-800 border-blue-400 text-blue-400';
    }
    return 'bg-gray-800/80 border-blue-400/30 text-white';
}
</script>
<template>
    <div class="space-y-3">
        <div class="space-y-3">
            <button v-for="opt in props.exercise.options" :key="opt" @click="toggle(opt)" :disabled="showFeedback"
                :class="[
                    'w-full text-center px-4 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                    getButtonClass(opt)
                ]">
                {{ opt }}
            </button>
        </div>
        <button @click="submit" :disabled="showFeedback || selected.length === 0"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
