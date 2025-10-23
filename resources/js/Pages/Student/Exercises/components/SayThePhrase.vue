<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const answer = ref('');
const isRecording = ref(false);
const error = ref('');
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const audioBlob = ref(null);
const isSending = ref(false);
const audioUrl = ref('');

// Mostrar la solución solo si showFeedback está activo
const showSolution = () => props.showFeedback && props.exercise.solution && props.exercise.solution[0];


async function startRecording() {
    error.value = '';
    audioChunks.value = [];
    audioBlob.value = null;
    audioUrl.value = '';
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const recorder = new MediaRecorder(stream);
        mediaRecorder.value = recorder;
        recorder.ondataavailable = (e) => {
            if (e.data.size > 0) {
                audioChunks.value.push(e.data);
            }
        };
        recorder.onstop = () => {
            audioBlob.value = new Blob(audioChunks.value, { type: 'audio/webm' });
            audioUrl.value = URL.createObjectURL(audioBlob.value);
        };
        recorder.start();
        isRecording.value = true;
    } catch (err) {
        error.value = 'No se pudo acceder al micrófono.';
    }
}

function stopRecording() {
    if (mediaRecorder.value && isRecording.value) {
        mediaRecorder.value.stop();
        isRecording.value = false;
    }
}


async function submit() {
    if (props.showFeedback) return;
    if (!audioBlob.value) {
        error.value = 'Debes grabar tu respuesta primero.';
        return;
    }
    isSending.value = true;
    error.value = '';
    try {
        const formData = new FormData();
        formData.append('audio', audioBlob.value, 'audio.webm');
        formData.append('exercise_id', props.exercise.id);
        formData.append('solution', props.exercise.solution[0] || '');
        formData.append('language', props.exercise.language || 'en');
        // Ajusta la URL según tu backend
        const response = await axios.post('/api/say-the-phrase/attempt', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        emit('answered', response.data);
    } catch (e) {
        error.value = 'Error al enviar el audio.';
    } finally {
        isSending.value = false;
    }
}
</script>

<template>
    <div class="space-y-7">
        <!-- Mic Button & Audio Controls -->
        <div class="flex flex-col items-center gap-4">
            <button type="button" @click="isRecording ? stopRecording() : startRecording()" :class="[
                'rounded-full flex items-center justify-center transition-all duration-200',
                isRecording ? 'bg-red-500 text-white animate-pulse' : 'bg-gray-300 dark:bg-gray-700',
                isRecording ? '' : 'hover:bg-indigo-400',
            ]" :style="{ width: '80px', height: '80px', fontSize: '2rem' }"
                :title="isRecording ? 'Detener grabación' : 'Iniciar grabación'"
                :disabled="props.showFeedback || isSending">
                <i :class="isRecording ? 'fa-solid fa-microphone' : 'fa-solid fa-microphone-lines'" />
            </button>
            <span v-if="isRecording" class="text-xs text-red-500">Grabando... Haz clic para detener</span>
            <span v-else-if="audioUrl" class="text-xs text-green-600">Grabación lista <i
                    class="fa-solid fa-circle-check"></i></span>
            <audio v-if="audioUrl" :src="audioUrl" controls class="mt-2" />
            <p v-if="error" class="text-xs text-red-400 mt-2">{{ error }}</p>
        </div>

        <!-- Comprobar Button -->
        <div class="flex justify-center">
            <button @click="submit" :disabled="props.showFeedback || !audioBlob || isRecording || isSending"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full max-w-xs mt-3">
                <i class="fa-solid fa-check mr-2"></i>
                <span v-if="isSending">Enviando...</span>
                <span v-else>Comprobar</span>
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
