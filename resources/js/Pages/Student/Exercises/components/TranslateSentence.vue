<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered', 'finish']);

const answer = ref('');
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

watch(() => props.showFeedback, (v) => { if (!v) answer.value = ''; });

function submit() {
    if (props.showFeedback || !answer.value.trim()) return;
    const expected = Array.isArray(props.exercise.solution) ? props.exercise.solution[0] : '';
    const isCorrect = expected && answer.value.trim().toLowerCase() === expected.trim().toLowerCase();
    emit('answered', isCorrect, answer.value);
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
}

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    if (e.key === 'Enter' && answer.value.trim()) {
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
        <div class="mb-2">
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-language text-gray-400"></i>
                Oración a traducir
            </div>
            <div
                class="mt-2 p-3 rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700">
                {{ exercise.prompt }}
            </div>
        </div>
        <div class="flex flex-col gap-2 w-full">
            <textarea v-model="answer" :disabled="showFeedback" rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                placeholder="Escribe aquí la traducción..."></textarea>
        </div>
        <div class="flex flex-col md:flex-row justify-between mt-4 w-full gap-2">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="hidden md:flex w-full md:w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <div class="hidden md:flex flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="showFeedback || !answer.trim()" :class="[
                'w-full md:w-1/3 flex items-center',
                (showFeedback || !answer.trim()) ? 'opacity-60 cursor-not-allowed' : ''
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
