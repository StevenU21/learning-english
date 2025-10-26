<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import CustomAudioPlayer from './CustomAudioPlayer.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false },
    unitSlug: { type: [String, Number], required: false }
});
const emit = defineEmits(['answered', 'finish']);

const selected = ref(null);
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
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

watch(() => props.showFeedback, (v) => { if (!v) selected.value = null; });

function handleKeydown(e) {
    if (props.showFeedback) return;
    if (showConfirm.value) return;
    if (window.innerWidth < 768) return;
    // Solo permitir 1 y 2
    const num = parseInt(e.key, 10);
    if (!isNaN(num) && num >= 1 && num <= 2) {
        choose(num === 1 ? 'Igual' : 'Distinto');
        return;
    }
    if (e.key === 'Enter' && selected.value !== null && !props.showFeedback) {
        submit();
    }
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function choose(value) {
    if (props.showFeedback) return;
    selected.value = value;
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(selected.value);
    emit('answered', isCorrect, selected.value);
}

function btnClass(option) {
    // Igual que MultipleChoice.vue
    const base = 'w-full flex items-center px-8 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70';
    if (props.showFeedback && selected.value === option) {
        const isCorrect = Array.isArray(props.exercise.solution) && props.exercise.solution.includes(option);
        return isCorrect
            ? 'bg-gray-800 border-green-500 text-green-400 ' + base
            : 'bg-gray-800 border-red-500 text-red-400 ' + base;
    }
    if (selected.value === option) {
        return 'bg-gray-800 border-blue-400 text-blue-400 ' + base;
    }
    // No seleccionado: borde azul claro con opacidad y fondo gris oscuro con opacidad
    return 'bg-gray-800/80 border-blue-400/30 text-white ' + base;
}
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <div class="gap-3 w-full">
            <div class="flex gap-2 w-full">
                <CustomAudioPlayer v-if="props.exercise.file_url" :src="props.exercise.file_url" class="w-full" />
            </div>
            <div class="flex gap-2 w-full">
                <CustomAudioPlayer v-if="props.exercise.file_b_url" :src="props.exercise.file_b_url" class="w-full" />
            </div>
        </div>
        <div class="flex flex-col gap-2 w-full">
            <button v-for="(opt, idx) in ['Igual', 'Distinto']" :key="opt" @click="choose(opt)" :disabled="showFeedback"
                :class="btnClass(opt)">
                <span v-if="windowWidth >= 768"
                    class="inline-block mr-4 px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                    style="min-width:2.2rem;text-align:center;">
                    {{ idx + 1 }}
                </span>
                <span class="flex-1 text-center">{{ opt }}</span>
            </button>
        </div>
        <div class="flex justify-between mt-4 w-full">
            <!-- Botón Terminar -->
            <DangerButton @click="handleFinish" class="hidden md:flex w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </DangerButton>

            <div class="flex-1"></div>

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
            <div class="bg-[#18232a] rounded-lg shadow-lg p-6 max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres salir?
                </p>
                <div class="flex gap-3 justify-center">
                    <SecondaryButton @click="showConfirm = false" class="flex items-center px-4 py-2">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        Cancelar
                    </SecondaryButton>
                    <DangerButton @click="confirmFinish" class="flex items-center px-4 py-2">
                        <i class="fa-solid fa-flag-checkered mr-2"></i>
                        Salir sin guardar
                    </DangerButton>
                </div>
            </div>
        </div>
    </div>
</template>
