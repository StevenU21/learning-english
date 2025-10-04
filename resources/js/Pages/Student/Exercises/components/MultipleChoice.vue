<script setup>
import { ref } from 'vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false },
});
const emit = defineEmits(['answered']);
const selected = ref(null);

function choose(opt) {
    if (props.showFeedback) return;
    selected.value = opt;
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    const isCorrect = Array.isArray(props.exercise.solution)
        ? props.exercise.solution.includes(selected.value)
        : false;
    emit('answered', isCorrect, selected.value);
}

function getButtonClass(opt) {
    if (props.showFeedback && selected.value === opt) {
        const isCorrect = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(opt) : false;
        return isCorrect
            ? 'bg-green-100 dark:bg-green-900 border-green-500 text-green-700 dark:text-green-300'
            : 'bg-red-100 dark:bg-red-900 border-red-500 text-red-700 dark:text-red-300';
    }
    if (selected.value === opt) {
        return 'bg-indigo-100 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-300';
    }
    return 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700';
}
</script>

<template>
    <div class="space-y-6">
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-200 text-center">{{ exercise.text }}</p>
        <div class="space-y-3">
            <button v-for="opt in exercise.options" :key="opt" @click="choose(opt)" :disabled="showFeedback" :class="[
                'w-full text-center px-4 py-3 rounded-xl border-2 text-sm font-bold transition-colors duration-150',
                'disabled:cursor-not-allowed disabled:opacity-70',
                getButtonClass(opt)
            ]">
                {{ opt }}
            </button>
        </div>
        <button @click="submit" :disabled="showFeedback || selected === null"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full mt-3">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
