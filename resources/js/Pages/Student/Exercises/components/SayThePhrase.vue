<script setup>
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const answer = ref('');
const isRecording = ref(false);
const recognition = ref(null);
const error = ref('');

// Mostrar la solución solo si showFeedback está activo
const showSolution = () => props.showFeedback && props.exercise.solution && props.exercise.solution[0];

function startRecording() {
    error.value = '';
    if (!('webkitSpeechRecognition' in window)) {
        error.value = 'Tu navegador no soporta reconocimiento de voz.';
        return;
    }
    recognition.value = new window.webkitSpeechRecognition();
    recognition.value.lang = 'es-ES';
    recognition.value.interimResults = false;
    recognition.value.maxAlternatives = 1;
    recognition.value.onresult = (event) => {
        answer.value = event.results[0][0].transcript;
    };
    recognition.value.onerror = (event) => {
        error.value = 'Error al grabar: ' + event.error;
    };
    recognition.value.onend = () => {
        isRecording.value = false;
    };
    isRecording.value = true;
    recognition.value.start();
}

function stopRecording() {
    if (recognition.value) {
        recognition.value.stop();
        isRecording.value = false;
    }
}

function submit() {
    if (props.showFeedback) return;
    emit('answered', answer.value);
}
</script>

<template>
    <div class="space-y-7">
        <!-- Prompt -->
        <div class="mb-2">
            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-300 text-base font-semibold">
                <i class="fa-solid fa-microphone-lines"></i>
                Frase que debes decir
            </div>
            <div class="mt-3 p-4 rounded-xl bg-indigo-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-indigo-100 dark:border-gray-700 text-lg font-medium shadow-sm">
                {{ exercise.prompt }}
            </div>
        </div>

        <!-- Mic Button & Answer -->
        <div class="flex flex-col items-center gap-4">
            <button @click="isRecording ? stopRecording() : startRecording()"
                :class="['rounded-full flex items-center justify-center transition-all duration-200', isRecording ? 'bg-red-500 animate-pulse scale-110' : 'bg-indigo-600 hover:bg-indigo-700 scale-100', props.showFeedback ? 'opacity-50 cursor-not-allowed' : '']"
                :disabled="props.showFeedback"
                style="width: 80px; height: 80px; font-size: 2rem;">
                <i class="fa-solid fa-microphone"></i>
            </button>
            <span v-if="isRecording" class="text-red-500 font-semibold">Grabando...</span>
            <p v-if="error" class="text-xs text-red-400 mt-2">{{ error }}</p>
        </div>

        <!-- Answer Card -->
        <div class="flex flex-col items-center">
            <div class="w-full max-w-xl">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm flex items-center gap-3">
                    <i class="fa-solid fa-user text-indigo-400"></i>
                    <input v-model="answer" :disabled="props.showFeedback" type="text"
                        class="flex-1 bg-transparent border-none focus:ring-0 text-lg text-gray-900 dark:text-gray-100 placeholder-gray-400"
                        placeholder="Tu respuesta aparecerá aquí..." />
                </div>
            </div>
        </div>

        <!-- Comprobar Button -->
        <div class="flex justify-center">
            <PrimaryButton @click="submit" :disabled="props.showFeedback || !answer.trim()" class="w-full max-w-xs mt-2 text-base py-3">
                <i class="fa-solid fa-check mr-2"></i> Comprobar
            </PrimaryButton>
        </div>

        <!-- Feedback Solution -->
        <div v-if="showSolution()"
            class="mt-4 p-4 rounded-xl bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-100 border border-green-200 dark:border-green-700 text-lg flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-lightbulb text-green-400"></i>
            <span><strong>Solución esperada:</strong> {{ props.exercise.solution[0] }}</span>
        </div>
    </div>
</template>
