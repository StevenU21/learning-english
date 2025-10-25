<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false },
});
import { router } from '@inertiajs/vue3';
import { ref as vueRef } from 'vue';
const emit = defineEmits(['answered', 'finish']);
const selected = ref(null);
const showConfirm = vueRef(false);
function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
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
            <button v-for="opt in exercise.options" :key="opt" @click="choose(opt)" :disabled="showFeedback" :class="[
                'w-full text-center px-8 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150',
                'disabled:cursor-not-allowed disabled:opacity-70',
                getButtonClass(opt)
            ]">
                {{ opt }}
            </button>
        </div>
        <div class="flex justify-between mt-4 w-full">
            <PrimaryButton @click="handleFinish" class="hidden md:block w-1/3">
                <i class="fa-solid fa-flag-checkered mr-2"></i> Terminar
            </PrimaryButton>
            <div class="flex-1"></div>
            <PrimaryButton @click="submit" :disabled="showFeedback || selected === null" class="w-full md:w-1/3">
                <i class="fa-solid fa-check mr-2"></i> Comprobar
            </PrimaryButton>
        </div>
        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-[#18232a] rounded-lg shadow-lg p-6 max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres salir?
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showConfirm = false"
                        class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-800 text-gray-200 font-semibold">Cancelar</button>
                    <button @click="confirmFinish"
                        class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">Salir sin
                        guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>
