<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered', 'finish']);

const userAnswerWords = ref([]);
const availableWords = ref([]);
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    if (e.key === 'Enter' && userAnswerWords.value.length > 0) {
        submit();
    }
}

onMounted(() => {
    window.addEventListener('resize', updateWindowWidth);
    window.addEventListener('keydown', handleKeydown);
    updateWindowWidth();
});
onUnmounted(() => {
    window.removeEventListener('resize', updateWindowWidth);
    window.removeEventListener('keydown', handleKeydown);
});

function initializeState() {
    userAnswerWords.value = [];
    availableWords.value = [...(props.exercise.options || [])].sort(() => Math.random() - 0.5);
}
watch(() => props.exercise, initializeState, { immediate: true });

function selectWord(word, index) {
    if (props.showFeedback) return;
    userAnswerWords.value.push(word);
    availableWords.value.splice(index, 1);
}

function deselectWord(word, index) {
    if (props.showFeedback) return;
    availableWords.value.push(word);
    userAnswerWords.value.splice(index, 1);
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
}

function submit() {
    if (props.showFeedback || userAnswerWords.value.length === 0) return;
    const solution = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const isCorrect = JSON.stringify(userAnswerWords.value) === JSON.stringify(solution);
    emit('answered', isCorrect, userAnswerWords.value);
}
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <!-- Pregunta -->
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ exercise.text }}</p>

        <!-- Área de respuesta del usuario -->
        <div
            class="min-h-[4rem] w-full border-b-2 border-gray-300 dark:border-gray-600 flex flex-wrap items-center gap-2 pb-2">
            <button v-for="(word, index) in userAnswerWords" :key="`${word}-${index}`"
                @click="deselectWord(word, index)" :disabled="showFeedback"
                class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ word }}
            </button>
        </div>

        <!-- Banco de palabras disponibles -->
        <div class="flex flex-wrap justify-center gap-2">
            <button v-for="(word, index) in availableWords" :key="`${word}-${index}`" @click="selectWord(word, index)"
                :disabled="showFeedback"
                class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-b-4 border-gray-300 dark:border-gray-600 font-semibold hover:bg-gray-100 dark:hover:bg-gray-600 transform active:translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ word }}
            </button>
        </div>

        <div class="flex justify-between mt-4 w-full">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="hidden md:flex w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <div class="flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="submit" :disabled="showFeedback || userAnswerWords.length === 0" :class="[
                'w-full md:w-1/3 flex items-center',
                (showFeedback || userAnswerWords.length === 0) ? 'opacity-60 cursor-not-allowed' : ''
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
