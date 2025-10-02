<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);
const selected = ref(null);

const questionText = computed(() => props.exercise?.text ?? props.exercise?.question ?? '');

watch(() => props.exercise, () => {
    selected.value = null;
}, { immediate: true });

watch(() => props.showFeedback, (value) => {
    if (!value) {
        selected.value = null;
    }
});

function answer(option) {
    if (props.showFeedback) return;
    selected.value = option;
    const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(option);
    emit('answered', isCorrect, option);
}

function getButtonClasses(option) {
    const base = 'w-full px-4 py-3 rounded-xl border-2 text-sm font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70';

    if (props.showFeedback && selected.value === option) {
        const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(option);
        return [
            base,
            isCorrect
                ? 'bg-indigo-100 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-300'
                : 'bg-amber-100 dark:bg-amber-900 border-amber-500 text-amber-700 dark:text-amber-300'
        ];
    }

    if (selected.value === option) {
        return [
            base,
            'bg-sky-100 dark:bg-sky-900 border-sky-500 text-sky-700 dark:text-sky-300'
        ];
    }

    return [
        base,
        'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
    ];
}
</script>

<template>
    <div class="space-y-6">
        <p class="text-lg font-semibold text-center text-gray-800 dark:text-gray-200">
            {{ questionText || 'Selecciona la respuesta correcta' }}
        </p>
        <div class="space-y-3">
            <button v-for="option in ['True', 'False']" :key="option" @click="answer(option)" :disabled="showFeedback"
                :class="getButtonClasses(option)">
                {{ option === 'True' ? 'Verdadero' : 'Falso' }}
            </button>
        </div>
    </div>
</template>
