<script setup>

import { ref, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered', 'finish']);
const inputVal = ref('');
const isDisabled = ref(false);
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}
onMounted(() => {
    window.addEventListener('resize', updateWindowWidth);
    updateWindowWidth();
});
onUnmounted(() => {
    window.removeEventListener('resize', updateWindowWidth);
});

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
}

function submit() {
    if (props.showFeedback || isDisabled.value || inputVal.value.trim() === '') return;
    const solution = Array.isArray(props.exercise.solution) ? props.exercise.solution.map(s => String(s).trim().toLowerCase()) : [];
    const correct = solution.includes(inputVal.value.trim().toLowerCase());
    emit('answered', correct, inputVal.value);
    isDisabled.value = true;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    if (e.key === 'Enter' && !isDisabled.value && inputVal.value.trim() !== '') {
        submit();
    }
}
</script>
<template>
    <div class="flex flex-col gap-6 w-full">
        <input v-model="inputVal" type="text" @keydown="handleKeydown"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"
            placeholder="Escribe tu respuesta" />

        <div class="flex justify-between mt-4 w-full">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="hidden md:flex w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <div class="flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="props.showFeedback || isDisabled.value || inputVal.trim() === ''"
                :class="[
                    'w-full md:w-1/3 flex items-center',
                    (props.showFeedback || isDisabled.value || inputVal.trim() === '') ? 'opacity-60 cursor-not-allowed' : ''
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
