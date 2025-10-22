<script setup>
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const answer = ref('');

watch(() => props.showFeedback, (v) => { if (!v) answer.value = ''; });

function submit() {
    if (props.showFeedback) return;
    const expected = Array.isArray(props.exercise.solution) ? props.exercise.solution[0] : '';
    const isCorrect = expected && answer.value.trim().toLowerCase() === expected.trim().toLowerCase();
    emit('answered', isCorrect, answer.value);
}
</script>

<template>
    <div class="space-y-5">
        <div class="mb-2">
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-language text-gray-400"></i>
                Oración a traducir
            </div>
            <div
                class="mt-2 p-3 rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700">
                {{ exercise.prompt }}
            </div>
        </div>
        <div class="space-y-3">
            <textarea v-model="answer" :disabled="showFeedback" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                placeholder="Escribe aquí la traducción..."></textarea>
        </div>
        <button @click="submit" :disabled="showFeedback || !answer.trim()"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full mt-3">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
