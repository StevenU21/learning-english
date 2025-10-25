<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import CustomAudioPlayer from './CustomAudioPlayer.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered', 'finish']);

const selected = ref(null);
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const audioKey = ref(0);

watch(() => props.showFeedback, (v) => {
    if (!v) selected.value = null;
});

watch(
    () => props.exercise,
    () => {
        selected.value = null;
        audioKey.value++;
    },
    { immediate: true }
);

function choose(opt) {
    if (props.showFeedback) return;
    selected.value = opt;
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(selected.value);
    emit('answered', isCorrect, selected.value);
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
}

function getButtonClass(opt) {
    const base = 'w-full flex items-center px-8 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70';
    if (props.showFeedback && selected.value === opt) {
        const correct = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(opt);
        return correct
            ? 'bg-gray-800 border-green-500 text-green-400 ' + base
            : 'bg-gray-800 border-red-500 text-red-400 ' + base;
    }
    if (selected.value === opt) return 'bg-gray-800 border-blue-400 text-blue-400 ' + base;
    return 'bg-gray-800/80 border-blue-400/30 text-white ' + base;
}

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    // Asignar número a cada opción
    const idx = parseInt(e.key, 10) - 1;
    if (!isNaN(idx) && idx >= 0 && idx < props.exercise.options.length) {
        choose(props.exercise.options[idx]);
        return;
    }
    if (e.key === 'Enter' && selected.value !== null) {
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
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <div class="flex flex-col items-center gap-2">
            <CustomAudioPlayer :key="audioKey" :src="props.exercise.file_url" v-if="props.exercise.file_url" />
        </div>
        <div class="flex flex-col gap-2 w-full">
            <button v-for="(opt, idx) in exercise.options" :key="opt" @click="choose(opt)" :disabled="showFeedback"
                :class="getButtonClass(opt)">
                <span v-if="windowWidth >= 768"
                    class="inline-block mr-4 px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                    style="min-width:2.2rem;text-align:center;">
                    {{ idx + 1 }}
                </span>
                <span class="flex-1 text-center">{{ opt }}</span>
            </button>
        </div>
        <div class="flex flex-col md:flex-row justify-between mt-4 w-full gap-2">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="hidden md:flex w-full md:w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <div class="hidden md:flex flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="showFeedback || selected === null" :class="[
                'w-full md:w-1/3 flex items-center',
                (showFeedback || selected === null) ? 'opacity-60 cursor-not-allowed' : ''
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
                    <button @click="showConfirm = false"
                        class="flex items-center px-4 py-2 rounded bg-gray-700 hover:bg-gray-800 text-gray-200 font-semibold">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        Cancelar
                    </button>
                    <button @click="confirmFinish"
                        class="flex items-center px-4 py-2 rounded bg-gray-700 hover:bg-gray-800 text-gray-200 font-semibold">
                        <i class="fa-solid fa-flag-checkered mr-2"></i>
                        Salir sin guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
