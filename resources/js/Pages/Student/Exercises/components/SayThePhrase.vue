<script setup>
import CustomAudioPlayer from './CustomAudioPlayer.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false },
    unitSlug: { type: [String, Number], required: false }
});
const emit = defineEmits(['answered', 'finish']);

const answer = ref('');
const isRecording = ref(false);
const error = ref('');
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const audioBlob = ref(null);
const isSending = ref(false);
const audioUrl = ref('');
const maxSeconds = 10;
const secondsLeft = ref(maxSeconds);
let timer = null;
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

// Mostrar la solución solo si showFeedback está activo
const showSolution = () => props.showFeedback && props.exercise.solution && props.exercise.solution[0];

// Limpiar el audio cuando cambie el ejercicio
watch(
    () => props.exercise.id,
    () => {
        audioChunks.value = [];
        audioBlob.value = null;
        audioUrl.value = '';
        error.value = '';
        isRecording.value = false;
        isSending.value = false;
        secondsLeft.value = maxSeconds;
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
            mediaRecorder.value.stop();
        }
        mediaRecorder.value = null;
    }
);

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    if (e.key === 'Enter' && audioBlob.value && !isRecording.value && !isSending.value) {
        submit();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', updateWindowWidth);
    updateWindowWidth();
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('resize', updateWindowWidth);
});

async function startRecording() {
    error.value = '';
    audioChunks.value = [];
    audioBlob.value = null;
    audioUrl.value = '';
    secondsLeft.value = maxSeconds;
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
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };
        recorder.start();
        isRecording.value = true;
        // Iniciar contador regresivo
        timer = setInterval(() => {
            if (secondsLeft.value > 0) {
                secondsLeft.value--;
            }
            if (secondsLeft.value <= 0) {
                stopRecording();
            }
        }, 1000);
    } catch (err) {
        error.value = 'No se pudo acceder al micrófono.';
    }
}

function stopRecording() {
    if (mediaRecorder.value && isRecording.value) {
        mediaRecorder.value.stop();
        isRecording.value = false;
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
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
        const response = await axios.post('/student/say-the-phrase/attempt', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        // Emit boolean correctness and transcription as user answer
        const isCorrect = (response.data.score ?? 0) >= 80;
        const transcription = response.data.transcription || '';
        emit('answered', isCorrect, transcription);
    } catch (e) {
        error.value = 'Error al enviar el audio.';
    } finally {
        isSending.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
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
            <Badge v-if="isRecording" type="error">
                <i class="fa-solid fa-microphone mr-1"></i>
                Grabando... <span class="ml-1 text-xs">{{ secondsLeft }}s</span>
                <span class="ml-1 text-xs">(máx. {{ maxSeconds }}s)</span>
            </Badge>
            <Badge v-else-if="audioUrl" type="success">
                <i class="fa-solid fa-circle-check mr-1"></i>
                Grabación lista
            </Badge>
            <div v-if="audioUrl" class="w-full flex flex-col items-center mt-2">
                <div class="w-full max-w-xs">
                    <CustomAudioPlayer :src="audioUrl" />
                </div>
            </div>
            <p v-if="error" class="text-xs text-red-400 mt-2">{{ error }}</p>
        </div>

        <div class="flex flex-col md:flex-row justify-between mt-4 w-full gap-2">
            <!-- Botón Terminar -->
            <DangerButton @click="handleFinish" class="hidden md:flex w-full md:w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </DangerButton>

            <div class="hidden md:flex flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="props.showFeedback || !audioBlob || isRecording || isSending"
                :class="[
                    'w-full md:w-1/3 flex items-center',
                    (props.showFeedback || !audioBlob || isRecording || isSending) ? 'opacity-60 cursor-not-allowed' : ''
                ]">
                <span v-if="windowWidth >= 768"
                    class="inline-flex items-center justify-center px-2 py-1 rounded border border-blue-400/60 text-base font-bold text-gray-800 bg-gray-100 select-none"
                    style="min-width:2.2rem; height:2.2rem; margin-right:0.75rem;">
                    ⏎
                </span>
                <span v-else class="inline-flex items-center">
                    <i class="fa-solid fa-check mr-2"></i>
                    Comprobar
                </span>
                <span v-if="windowWidth >= 768" class="flex-1 text-center">Comprobar</span>
            </PrimaryButton>
        </div>

        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-[#18232a] rounded-lg shadow-lg p-6 max-w-full md:max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres salir?
                </p>
                <div class="flex gap-3 justify-center">
                    <SecondaryButton @click="showConfirm = false" class="w-auto px-4 py-2">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        Cancelar
                    </SecondaryButton>
                    <DangerButton @click="confirmFinish" class="w-auto px-4 py-2">
                        <i class="fa-solid fa-flag-checkered mr-2"></i>
                        Salir sin guardar
                    </DangerButton>
                </div>
            </div>
        </div>
    </div>
</template>
