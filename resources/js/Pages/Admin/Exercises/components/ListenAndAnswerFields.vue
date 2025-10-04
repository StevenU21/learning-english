<script setup>
import FileInput from '@/Components/FileInput.vue';
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

// force options for display purposes; backend will also enforce
props.form.options = ['Igual', 'Distinto'];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function selectSolution(value) {
    props.form.solution = [value];
}

// Audio A preview
const audioA = ref(null);
const isPlayingA = ref(false);
const urlA = ref(null);
const lastObjectUrlA = ref(null);

// Audio B preview
const audioB = ref(null);
const isPlayingB = ref(false);
const urlB = ref(null);
const lastObjectUrlB = ref(null);

function setupAudio(elRef, url, playingRef) {
    if (!elRef.value) {
        elRef.value = new Audio();
        elRef.value.addEventListener('ended', () => playingRef.value = false);
        elRef.value.addEventListener('pause', () => playingRef.value = false);
        elRef.value.addEventListener('play', () => playingRef.value = true);
    }
    if (elRef.value.src !== url) elRef.value.src = url;
}

function resetAudio(elRef, playingRef) {
    try { if (elRef.value) elRef.value.pause(); } catch { /* noop */ }
    playingRef.value = false;
}

function togglePlayA() {
    if (!urlA.value) return;
    setupAudio(audioA, urlA.value, isPlayingA);
    if (isPlayingA.value) audioA.value.pause(); else audioA.value.play();
}

function togglePlayB() {
    if (!urlB.value) return;
    setupAudio(audioB, urlB.value, isPlayingB);
    if (isPlayingB.value) audioB.value.pause(); else audioB.value.play();
}

watch(() => props.form.file, (file) => {
    resetAudio(audioA, isPlayingA);
    if (lastObjectUrlA.value) { URL.revokeObjectURL(lastObjectUrlA.value); lastObjectUrlA.value = null; }
    if (file instanceof File) {
        const u = URL.createObjectURL(file);
        urlA.value = u; lastObjectUrlA.value = u;
    } else {
        urlA.value = props.form.file_url ?? null;
    }
}, { immediate: true });

watch(() => props.form.file_b, (file) => {
    resetAudio(audioB, isPlayingB);
    if (lastObjectUrlB.value) { URL.revokeObjectURL(lastObjectUrlB.value); lastObjectUrlB.value = null; }
    if (file instanceof File) {
        const u = URL.createObjectURL(file);
        urlB.value = u; lastObjectUrlB.value = u;
    } else {
        urlB.value = props.form.file_b_url ?? null;
    }
}, { immediate: true });

onBeforeUnmount(() => {
    resetAudio(audioA, isPlayingA); if (lastObjectUrlA.value) URL.revokeObjectURL(lastObjectUrlA.value);
    resetAudio(audioB, isPlayingB); if (lastObjectUrlB.value) URL.revokeObjectURL(lastObjectUrlB.value);
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-music text-gray-400"></i>
                Primer audio (obligatorio)
            </div>
            <FileInput v-model="form.file" accept="audio/*" />
            <div class="mt-2">
                <button type="button" @click="togglePlayA" :disabled="!urlA"
                    class="inline-flex items-center rounded-md border border-transparent px-3 py-1.5 text-xs font-semibold uppercase tracking-widest transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isPlayingA ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                    <i :class="isPlayingA ? 'fa-solid fa-pause mr-2' : 'fa-solid fa-play mr-2'"></i>
                    {{ isPlayingA ? 'Pausar' : 'Reproducir' }} A
                </button>
            </div>
            <p v-if="errors.file" class="text-xs text-red-400 mt-1">{{ errors.file }}</p>
        </div>

        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-music text-gray-400"></i>
                Segundo audio (obligatorio)
            </div>
            <FileInput v-model="form.file_b" accept="audio/*" />
            <div class="mt-2">
                <button type="button" @click="togglePlayB" :disabled="!urlB"
                    class="inline-flex items-center rounded-md border border-transparent px-3 py-1.5 text-xs font-semibold uppercase tracking-widest transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isPlayingB ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                    <i :class="isPlayingB ? 'fa-solid fa-pause mr-2' : 'fa-solid fa-play mr-2'"></i>
                    {{ isPlayingB ? 'Pausar' : 'Reproducir' }} B
                </button>
            </div>
            <p v-if="errors.file_b" class="text-xs text-red-400 mt-1">{{ errors.file_b }}</p>
        </div>

        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-check-double text-gray-400"></i>
                Respuesta correcta
            </div>
            <div class="flex gap-4 mt-2">
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="radio" name="listen_answer" value="Igual" @change="selectSolution('Igual')"
                        :checked="form.solution?.[0] === 'Igual'"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                    Igual
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="radio" name="listen_answer" value="Distinto" @change="selectSolution('Distinto')"
                        :checked="form.solution?.[0] === 'Distinto'"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                    Distinto
                </label>
            </div>
            <p v-if="errors.solution" class="text-xs text-red-400 mt-1">{{ errors.solution }}</p>
        </div>
    </div>
</template>
