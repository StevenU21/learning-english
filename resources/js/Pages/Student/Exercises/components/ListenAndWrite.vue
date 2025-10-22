<script setup>
import { ref, watch, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const answer = ref('');
const isPlaying = ref(false);
let audio = null;

watch(() => props.showFeedback, (v) => { if (!v) answer.value = ''; });

function togglePlay() {
    const url = props.exercise.file_url;
    if (!url) return;
    if (!audio) {
        audio = new Audio(url);
        audio.addEventListener('ended', () => { isPlaying.value = false; });
    }
    if (audio.src !== url) {
        audio.pause();
        audio = new Audio(url);
        audio.addEventListener('ended', () => { isPlaying.value = false; });
    }
    if (isPlaying.value) {
        audio.pause();
        isPlaying.value = false;
    } else {
        audio.play();
        isPlaying.value = true;
    }
}

function submit() {
    if (props.showFeedback) return;
    if (audio && isPlaying.value) {
        audio.pause();
        isPlaying.value = false;
    }
    // La solución es un arreglo, se compara con answer.value (puede ser case-insensitive y trim)
    const expected = Array.isArray(props.exercise.solution) ? props.exercise.solution[0] : '';
    const isCorrect = expected && answer.value.trim().toLowerCase() === expected.trim().toLowerCase();
    emit('answered', isCorrect, answer.value);
}
</script>

<template>
    <div class="space-y-5">
        <div class="flex items-center justify-center gap-3">
            <PrimaryButton type="button" @click="togglePlay" class="flex items-center gap-2">
                <i :class="['fa-solid', isPlaying ? 'fa-pause' : 'fa-play']"></i>
                {{ isPlaying ? 'Pausar audio' : 'Reproducir audio' }}
            </PrimaryButton>
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
