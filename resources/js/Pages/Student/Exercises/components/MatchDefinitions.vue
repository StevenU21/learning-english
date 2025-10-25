<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    exercise: Object,
    showFeedback: Boolean,
    lastAnswer: Array
});
const emit = defineEmits(['answered']);

function getConceptos() {
    return Array.isArray(props.exercise?.options)
        ? [...new Set(props.exercise.options.map(opt => opt.concepto).filter(v => v))]
        : [];
}
function getDefiniciones() {
    return Array.isArray(props.exercise?.options)
        ? [...new Set(props.exercise.options.map(opt => opt.definicion).filter(v => v))]
        : [];
}

const conceptos = ref(getConceptos());
const definiciones = ref(getDefiniciones().sort(() => Math.random() - 0.5));
const selectedConcepto = ref(null);
const selectedDefinicion = ref(null);
const studentMatches = ref([]); // [{concepto, definicion}]
const feedback = ref({});       // { concepto: true/false }
const validated = ref(false);   // bloquear tras validar (un intento)

watch(() => props.exercise, () => {
    conceptos.value = getConceptos();
    definiciones.value = getDefiniciones().sort(() => Math.random() - 0.5);
    selectedConcepto.value = null;
    selectedDefinicion.value = null;
    studentMatches.value = [];
    feedback.value = {};
    validated.value = false;
}, { immediate: true });

function solutionArray() {
    return Array.isArray(props.exercise?.solution)
        ? props.exercise.solution
        : props.exercise?.solution ? [props.exercise.solution] : [];
}

function validatePair(concepto, definicion) {
    return solutionArray().some(sol => sol.concepto === concepto && sol.definicion === definicion);
}

function selectConcepto(concepto) {
    if (props.showFeedback || validated.value) return;
    selectedConcepto.value = concepto;
}
function selectDefinicion(definicion) {
    if (props.showFeedback || validated.value) return;
    selectedDefinicion.value = definicion;
    if (selectedConcepto.value && selectedDefinicion.value) {
        const idx = studentMatches.value.findIndex(p => p.concepto === selectedConcepto.value);
        if (idx !== -1) {
            studentMatches.value[idx].definicion = selectedDefinicion.value;
        } else {
            studentMatches.value.push({ concepto: selectedConcepto.value, definicion: selectedDefinicion.value });
        }
        feedback.value[selectedConcepto.value] = validatePair(selectedConcepto.value, selectedDefinicion.value);
        // Si la pareja es incorrecta, validar y emitir respuesta incorrecta
        if (feedback.value[selectedConcepto.value] === false && !validated.value) {
            validated.value = true;
            emit('answered', false, JSON.parse(JSON.stringify(studentMatches.value)));
        }
        // Si la pareja es correcta, bloquear ese concepto y definicion (ya se marca en verde)
        if (feedback.value[selectedConcepto.value] === true) {
            // No hacer nada extra, ya se marca en verde y se bloquea por los disabled
        }
        selectedConcepto.value = null;
        selectedDefinicion.value = null;
    }
}

function isCorrectConcepto(concepto) { return feedback.value[concepto] === true; }
function isIncorrectConcepto(concepto) { return feedback.value[concepto] === false; }

// Add functions for definitions feedback
function isCorrectDefinicion(definicion) {
    const pair = studentMatches.value.find(p => p.definicion === definicion);
    return pair ? feedback.value[pair.concepto] === true : false;
}
function isIncorrectDefinicion(definicion) {
    const pair = studentMatches.value.find(p => p.definicion === definicion);
    return pair ? feedback.value[pair.concepto] === false : false;
}

function checkAnswer() {
    if (validated.value) return;
    const sol = solutionArray();
    const isCorrect = studentMatches.value.every(pair => sol.some(s => s.concepto === pair.concepto && s.definicion === pair.definicion))
        && studentMatches.value.length === sol.length;
    validated.value = true;
    emit('answered', isCorrect, JSON.parse(JSON.stringify(studentMatches.value)));
}
</script>

<template>
    <div class="space-y-4 mx-2 sm:mx-0">
        <div v-if="conceptos.length && definiciones.length" class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
            <!-- Conceptos -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="concepto in conceptos" :key="concepto" @click="selectConcepto(concepto)"
                        :disabled="props.showFeedback || validated || studentMatches.some(p => p.concepto === concepto)"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            props.showFeedback && isCorrectConcepto(concepto) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            props.showFeedback && isIncorrectConcepto(concepto) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !props.showFeedback && selectedConcepto === concepto ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !props.showFeedback && studentMatches.some(p => p.concepto === concepto) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !props.showFeedback && !studentMatches.some(p => p.concepto === concepto) && selectedConcepto !== concepto ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        {{ concepto }}
                    </button>
                </div>
            </div>
            <!-- Definiciones -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="definicion in definiciones" :key="definicion" @click="selectDefinicion(definicion)"
                        :disabled="props.showFeedback || validated || studentMatches.some(p => p.definicion === definicion)"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            props.showFeedback && isCorrectDefinicion(definicion) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            props.showFeedback && isIncorrectDefinicion(definicion) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !props.showFeedback && selectedDefinicion === definicion ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !props.showFeedback && studentMatches.some(p => p.definicion === definicion) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !props.showFeedback && !studentMatches.some(p => p.definicion === definicion) && selectedDefinicion !== definicion ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        {{ definicion }}
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button @click="checkAnswer" :disabled="props.showFeedback || validated || !studentMatches.length"
                class="flex-1 inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-check mr-2"></i> Comprobar respuesta
            </button>
        </div>
    </div>
</template>
