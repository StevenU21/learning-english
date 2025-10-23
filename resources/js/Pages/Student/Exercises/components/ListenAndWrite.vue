<script setup>
import { ref, watch } from 'vue';
import CustomAudioPlayer from './CustomAudioPlayer.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);


const answer = ref('');


watch(() => props.showFeedback, (v) => { if (!v) answer.value = ''; });


// El audio se controla desde CustomAudioPlayer

function submit() {
    if (props.showFeedback) return;
    // El audio se controla desde CustomAudioPlayer
    // La solución es un arreglo, se compara con answer.value (puede ser case-insensitive y trim)
    const expected = Array.isArray(props.exercise.solution) ? props.exercise.solution[0] : '';
    const isCorrect = expected && answer.value.trim().toLowerCase() === expected.trim().toLowerCase();
    emit('answered', isCorrect, answer.value);
}
</script>

<template>
    <div class="space-y-5">
        <div class="flex gap-3">
            <CustomAudioPlayer v-if="props.exercise.file_url" :src="props.exercise.file_url" />
        </div>
        <div class="space-y-3">
            <textarea v-model="answer" :disabled="showFeedback" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                placeholder="Escribe aquí lo que escuchas..."></textarea>
        </div>
        <button @click="submit" :disabled="showFeedback || !answer.trim()"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full mt-3">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
