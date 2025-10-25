<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';

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
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(selected.value);
    emit('answered', isCorrect, selected.value);
}

function getButtonClasses(option) {
    const base = 'w-full px-4 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70';

    // Feedback mode: show correct/incorrect colors
    if (props.showFeedback && selected.value === option) {
        const isCorrect = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(option) : false;
        return [
            base,
            isCorrect
                ? 'bg-gray-800 border-green-500 text-green-400'
                : 'bg-gray-800 border-red-500 text-red-400'
        ];
    }

    // Selected but not feedback
    if (selected.value === option) {
        return [
            base,
            'bg-gray-800 border-blue-400 text-blue-400'
        ];
    }

    // Not selected
    return [
        base,
        'bg-gray-800/80 border-blue-400/30 text-white'
    ];
}

function handleKeydown(e) {
    if (props.showFeedback) return;
    // 1 = True, 2 = False
    if (e.key === '1') {
        answer('True');
        return;
    }
    if (e.key === '2') {
        answer('False');
        return;
    }
    if (e.key === 'Enter' && selected.value !== null) {
        submit();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-3">
            <button v-for="(option, idx) in ['True', 'False']" :key="option" @click="answer(option)"
                :disabled="showFeedback" :class="getButtonClasses(option)" style="display: flex; align-items: center;">
                <span
                    class="inline-block px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                    style="min-width:2.2rem;text-align:left; margin-right:0.75rem;">
                    {{ idx + 1 }}
                </span>
                <span style="flex:1; text-align:center;">{{ option === 'True' ? 'Verdadero' : 'Falso' }}</span>
            </button>
        </div>
        <button @click="submit" :disabled="showFeedback || selected === null"
            class="inline-flex items-center justify-start rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full mt-3">
            <span
                class="inline-block px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                style="min-width:2.2rem;text-align:left; margin-right:0.75rem;">⏎</span>
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
