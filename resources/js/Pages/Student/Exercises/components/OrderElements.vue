<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    showFeedback: { type: Boolean, default: false }
});
const emit = defineEmits(['answered']);

// Palabras que el usuario ha seleccionado para su respuesta
const userAnswerWords = ref([]);
// Palabras disponibles en el banco de opciones
const availableWords = ref([]);

// Inicializa o resetea el estado cuando el ejercicio cambia
function initializeState() {
    userAnswerWords.value = [];
    // Desordena las opciones para que no aparezcan siempre en el mismo orden
    availableWords.value = [...(props.exercise.options || [])].sort(() => Math.random() - 0.5);
}

// Observa si el ejercicio cambia para reiniciar el componente
watch(() => props.exercise, initializeState, { immediate: true });

// Mueve una palabra del banco a la respuesta del usuario
function selectWord(word, index) {
    if (props.showFeedback) return;
    userAnswerWords.value.push(word);
    availableWords.value.splice(index, 1);
}

// Devuelve una palabra de la respuesta al banco
function deselectWord(word, index) {
    if (props.showFeedback) return;
    availableWords.value.push(word);
    userAnswerWords.value.splice(index, 1);
}

function submit() {
    if (props.showFeedback) return;
    const solution = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const isCorrect = JSON.stringify(userAnswerWords.value) === JSON.stringify(solution);
    emit('answered', isCorrect, userAnswerWords.value);
}
</script>

<template>
    <div class="space-y-6">
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

        <!-- Botón de comprobación -->
        <button @click="submit" :disabled="showFeedback || userAnswerWords.length === 0"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
