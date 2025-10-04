<script setup>
import FileInput from '@/Components/FileInput.vue';
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

// Audio preview state
const audio = ref(null);
const isPlaying = ref(false);
const previewUrl = ref(null);
const lastObjectUrl = ref(null);

function resetAudio() {
    try {
        if (audio.value) {
            audio.value.pause();
        }
    } catch { /* noop */ }
    isPlaying.value = false;
}

function setupAudio(url) {
    if (!audio.value) {
        audio.value = new Audio();
        audio.value.addEventListener('ended', () => isPlaying.value = false);
        audio.value.addEventListener('pause', () => isPlaying.value = false);
        audio.value.addEventListener('play', () => isPlaying.value = true);
    }
    if (audio.value.src !== url) {
        audio.value.src = url;
    }
}

function togglePlay() {
    if (!previewUrl.value) return;
    setupAudio(previewUrl.value);
    if (isPlaying.value) audio.value.pause(); else audio.value.play();
}

watch(() => props.form.file, (file) => {
    // Clear previous
    resetAudio();
    if (lastObjectUrl.value) {
        URL.revokeObjectURL(lastObjectUrl.value);
        lastObjectUrl.value = null;
    }
    if (file instanceof File) {
        const url = URL.createObjectURL(file);
        previewUrl.value = url;
        lastObjectUrl.value = url;
    } else {
        // try to use existing URL if provided (on edit)
        previewUrl.value = props.form.file_url ?? null;
    }
}, { immediate: true });

onBeforeUnmount(() => {
    resetAudio();
    if (lastObjectUrl.value) URL.revokeObjectURL(lastObjectUrl.value);
});

function addOption() {
    if (props.form.options.length >= 4) return;
    props.form.options.push('');
}
function removeOption(idx) {
    const removed = String(props.form.options[idx] ?? '');
    props.form.options.splice(idx, 1);
    // If removed option was selected, clear selection
    if (props.form.solution?.[0] && props.form.solution[0] === removed) {
        props.form.solution = [];
    }
}
function selectSolution(idx) {
    const value = String(props.form.options[idx] ?? '');
    props.form.solution = [value];
}
function isSelected(opt) {
    return props.form.solution?.[0] === String(opt);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Audio principal -->
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-music text-gray-400"></i>
                Audio (obligatorio)
            </div>
            <FileInput v-model="form.file" accept="audio/*" />
            <div class="mt-2">
                <button type="button" @click="togglePlay" :disabled="!previewUrl"
                    class="inline-flex items-center rounded-md border border-transparent px-3 py-1.5 text-xs font-semibold uppercase tracking-widest transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isPlaying ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                    <i :class="isPlaying ? 'fa-solid fa-pause mr-2' : 'fa-solid fa-play mr-2'"></i>
                    {{ isPlaying ? 'Pausar' : 'Reproducir' }}
                </button>
            </div>
            <p v-if="errors.file" class="text-xs text-red-400 mt-1">{{ errors.file }}</p>
        </div>

        <!-- Opciones -->
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-list text-gray-400"></i>
                Opciones (2 a 4)
            </div>
            <div class="space-y-3 mt-2">
                <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-3">
                    <input v-model="form.options[idx]" type="text"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        placeholder="Opción" />
                    <label class="flex items-center gap-1 text-xs text-gray-300">
                        <input type="radio" name="solution" :checked="isSelected(opt)" @change="selectSolution(idx)"
                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                        Correcta
                    </label>
                    <button type="button" @click="removeOption(idx)"
                        class="text-xs text-red-400 hover:text-red-300">Quitar</button>
                </div>
            </div>
            <div class="flex gap-3 mt-2">
                <button type="button" @click="addOption" :disabled="form.options.length >= 4"
                    class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-plus mr-1"></i> Agregar opción
                </button>
            </div>
            <p v-if="errors.options" class="text-xs text-red-400 mt-1">{{ errors.options }}</p>
            <p v-if="errors.solution" class="text-xs text-red-400 mt-1">{{ errors.solution }}</p>
        </div>
    </div>

</template>
