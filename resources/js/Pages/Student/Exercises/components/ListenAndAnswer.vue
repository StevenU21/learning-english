<script setup>
import { ref, watch, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

const selected = ref(null);
const isPlaying = ref(false);
const current = ref(null);
let audio = null;

watch(() => props.showFeedback, (v) => { if (!v) selected.value = null; });

function choose(value) {
    if (props.showFeedback) return;
    selected.value = value;
}

function togglePlay(which = 'a') {
    const url = which === 'a' ? props.exercise.file_url : props.exercise.file_b_url;
    if (!url) return;
    // switching track
    if (!audio || current.value !== which) {
        if (audio) audio.pause();
        audio = new Audio(url);
        audio.addEventListener('ended', () => {
            isPlaying.value = false;
            current.value = null;
        });
        current.value = which;
        audio.play();
        isPlaying.value = true;
    } else {
        // same track toggle play/pause
        if (isPlaying.value) {
            audio.pause();
            isPlaying.value = false;
        } else {
            audio.play();
            isPlaying.value = true;
        }
    }
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    // stop audio when submitting
    if (audio && isPlaying.value) {
        audio.pause();
        isPlaying.value = false;
    }
    const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(selected.value);
    emit('answered', isCorrect, selected.value);
}

function btnClass(option) {
    const base = 'w-full px-4 py-3 rounded-xl border-2 text-sm font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70';
    if (props.showFeedback && selected.value === option) {
        const ok = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(option);
        return [base, ok ? 'bg-green-100 dark:bg-green-900 border-green-500 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 border-red-500 text-red-700 dark:text-red-300'];
    }
    if (selected.value === option) return [base, 'bg-indigo-100 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-300'];
    return [base, 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'];
}
</script>

<template>
    <div class="space-y-5">
        <div class="flex items-center justify-center gap-3">
            <PrimaryButton type="button" @click="togglePlay('a')" class="flex items-center gap-2">
                <i :class="['fa-solid', current === 'a' && isPlaying ? 'fa-pause' : 'fa-play']"></i>
                {{ current === 'a' && isPlaying ? 'Pausar audio A' : 'Audio A' }}
            </PrimaryButton>
            <PrimaryButton type="button" @click="togglePlay('b')" class="flex items-center gap-2">
                <i :class="['fa-solid', current === 'b' && isPlaying ? 'fa-pause' : 'fa-play']"></i>
                {{ current === 'b' && isPlaying ? 'Pausar audio B' : 'Audio B' }}
            </PrimaryButton>
        </div>
        <div class="space-y-3">
            <button @click="choose('Igual')" :disabled="showFeedback" :class="btnClass('Igual')">Igual</button>
            <button @click="choose('Distinto')" :disabled="showFeedback" :class="btnClass('Distinto')">Distinto</button>
        </div>
        <button @click="submit" :disabled="showFeedback || selected === null"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full mt-3">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
