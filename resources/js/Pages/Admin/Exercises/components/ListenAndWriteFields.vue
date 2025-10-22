<script setup>
import FileInput from '@/Components/FileInput.vue';
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (!Array.isArray(props.form.solution)) props.form.solution = [];

// Audio preview
const audio = ref(null);
const isPlaying = ref(false);
const url = ref(null);
const lastObjectUrl = ref(null);

function setupAudio() {
    if (!audio.value) {
        audio.value = new Audio();
        audio.value.addEventListener('ended', () => isPlaying.value = false);
        audio.value.addEventListener('pause', () => isPlaying.value = false);
        audio.value.addEventListener('play', () => isPlaying.value = true);
    }
    if (audio.value.src !== url.value) audio.value.src = url.value;
}

function resetAudio() {
    try { if (audio.value) audio.value.pause(); } catch { /* noop */ }
    isPlaying.value = false;
}

function togglePlay() {
    if (!url.value) return;
    setupAudio();
    if (isPlaying.value) audio.value.pause(); else audio.value.play();
}

watch(() => props.form.file, (file) => {
    resetAudio();
    if (lastObjectUrl.value) { URL.revokeObjectURL(lastObjectUrl.value); lastObjectUrl.value = null; }
    if (file instanceof File) {
        const u = URL.createObjectURL(file);
        url.value = u; lastObjectUrl.value = u;
    } else {
        url.value = props.form.file_url ?? null;
    }
}, { immediate: true });

onBeforeUnmount(() => {
    resetAudio();
    if (lastObjectUrl.value) URL.revokeObjectURL(lastObjectUrl.value);
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-music text-gray-400"></i>
                Audio (obligatorio)
            </div>
            <FileInput v-model="form.file" accept="audio/*" />
            <div class="mt-2">
                <button type="button" @click="togglePlay" :disabled="!url"
                    class="inline-flex items-center rounded-md border border-transparent px-3 py-1.5 text-xs font-semibold uppercase tracking-widest transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isPlaying ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                    <i :class="isPlaying ? 'fa-solid fa-pause mr-2' : 'fa-solid fa-play mr-2'"></i>
                    {{ isPlaying ? 'Pausar' : 'Reproducir' }}
                </button>
            </div>
            <p v-if="errors.file" class="text-xs text-red-400 mt-1">{{ errors.file }}</p>
        </div>

        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-keyboard text-gray-400"></i>
                Transcripción esperada
            </div>
            <textarea v-model="form.solution[0]" rows="3"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"
                placeholder="Escribe aquí la transcripción esperada..."></textarea>
            <p v-if="errors.solution" class="text-xs text-red-400 mt-1">{{ errors.solution }}</p>
        </div>
    </div>
</template>
