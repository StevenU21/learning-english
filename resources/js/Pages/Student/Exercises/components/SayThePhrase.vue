<script setup>
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const answer = ref('');
const isRecording = ref(false); // Placeholder for future use
const error = ref('');

// Mostrar la solución solo si showFeedback está activo
const showSolution = () => props.showFeedback && props.exercise.solution && props.exercise.solution[0];

// Funciones de grabación deshabilitadas por ahora
function startRecording() {
    error.value = 'Funcionalidad de grabación no disponible.';
}
function stopRecording() {
    // No-op
}

function submit() {
    if (props.showFeedback) return;
    emit('answered', answer.value);
}
</script>

<template>
    <div class="space-y-7">
        <!-- Mic Button & Answer -->
        <div class="flex flex-col items-center gap-4">
            <button type="button" @click="startRecording"
                class="rounded-full flex items-center justify-center transition-all duration-200 bg-gray-300 dark:bg-gray-700 opacity-60 cursor-not-allowed"
                :disabled="true" style="width: 80px; height: 80px; font-size: 2rem;" title="Grabación no disponible">
                <i class="fa-solid fa-microphone-slash"></i>
            </button>
            <span class="text-xs text-gray-400">La grabación de voz no está disponible.</span>
            <p v-if="error" class="text-xs text-red-400 mt-2">{{ error }}</p>
        </div>

        <!-- Comprobar Button -->
        <div class="flex justify-center">
            <button @click="submit" :disabled="props.showFeedback || !answer.trim()"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full max-w-xs mt-3">
                <i class="fa-solid fa-check mr-2"></i> Comprobar
            </button>
        </div>

        <!-- Feedback Solution -->
        <div v-if="showSolution()"
            class="mt-4 p-4 rounded-xl bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-100 border border-green-200 dark:border-green-700 text-lg flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-lightbulb text-green-400"></i>
            <span><strong>Solución esperada:</strong> {{ props.exercise.solution[0] }}</span>
        </div>
    </div>
</template>
