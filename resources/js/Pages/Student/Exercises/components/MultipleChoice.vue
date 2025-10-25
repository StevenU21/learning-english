<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false },
    unitSlug: { type: [String, Number], required: false },
});
import { router } from '@inertiajs/vue3';
import { ref as vueRef } from 'vue';
const emit = defineEmits(['answered', 'finish']);
const selected = ref(null);
const showConfirm = vueRef(false);
// Track window width reactively for shortcut indicator
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
// Keyboard shortcut logic
function handleKeydown(e) {
    // Deshabilitar submit con Enter si showFeedback está activo (ya comprobado)
    if (props.showFeedback) return;
    // Only allow shortcuts if not showing confirm dialog
    if (showConfirm.value) return;
    // Only run on desktop (ignore if window.innerWidth < 768)
    if (window.innerWidth < 768) return;
    // Only allow number keys 1 to options.length
    const num = parseInt(e.key, 10);
    if (!isNaN(num) && num >= 1 && num <= props.exercise.options.length) {
        choose(props.exercise.options[num - 1]);
        return;
    }
    // Allow Enter to submit if something is selected and feedback is not shown
    if (e.key === 'Enter' && selected.value !== null && !props.showFeedback) {
        submit();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    // Usar el unitSlug pasado desde Sequence.vue
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function choose(opt) {
    if (props.showFeedback) return;
    selected.value = opt;
}

function submit() {
    if (props.showFeedback || selected.value === null) return;
    const isCorrect = Array.isArray(props.exercise.solution)
        ? props.exercise.solution.includes(selected.value)
        : false;
    emit('answered', isCorrect, selected.value);
}

function getButtonClass(opt) {
    if (props.showFeedback && selected.value === opt) {
        const isCorrect = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(opt) : false;
        return isCorrect
            ? 'bg-gray-800 border-green-500 text-green-400'
            : 'bg-gray-800 border-red-500 text-red-400';
    }
    if (selected.value === opt) {
        return 'bg-gray-800 border-blue-400 text-blue-400';
    }
    // No seleccionado: borde azul claro con opacidad y fondo gris oscuro con opacidad
    return 'bg-gray-800/80 border-blue-400/30 text-white';
}
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <p class="text-base font-semibold text-white text-center">{{ exercise.text }}</p>
        <div class="flex flex-col gap-2 w-full">
            <button v-for="(opt, idx) in exercise.options" :key="opt" @click="choose(opt)" :disabled="showFeedback"
                :class="[
                    'w-full flex items-center px-8 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150',
                    'disabled:cursor-not-allowed disabled:opacity-70',
                    getButtonClass(opt)
                ]">
                <span v-if="windowWidth >= 768"
                    class="inline-block mr-4 px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                    style="min-width:2.2rem;text-align:center;">
                    {{ idx + 1 }}
                </span>
                <span class="flex-1 text-center">{{ opt }}</span>
            </button>
        </div>
        <div class="flex flex-row justify-between mt-4 w-full gap-2">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="flex w-1/2 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="showFeedback || selected === null" :class="[
                'flex w-1/2 items-center',
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres
                    salir?
                </p>
                <div class="flex gap-2 justify-center">
                    <PrimaryButton @click="showConfirm = false" class="w-auto px-3 py-2">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        Cancelar
                    </PrimaryButton>
                    <PrimaryButton @click="confirmFinish" class="w-auto px-3 py-2">
                        <i class="fa-solid fa-flag-checkered mr-2"></i>
                        Salir sin guardar
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </div>
</template>
