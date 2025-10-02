<script setup>
import { ref, watch } from 'vue';

// Nueva lógica solicitada: selección de pares con validación inmediata e intento único.
// Ajustado para mantener el contrato de este entorno (emitir 'answered').
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
        if (feedback.value[selectedConcepto.value] === false && !validated.value) {
            validated.value = true;
            emit('answered', false, JSON.parse(JSON.stringify(studentMatches.value)));
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
    <div class="space-y-4">
        <div v-if="conceptos.length && definiciones.length" class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
            <!-- Conceptos -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="concepto in conceptos" :key="concepto" @click="selectConcepto(concepto)"
                        :disabled="props.showFeedback || validated || studentMatches.some(p => p.concepto === concepto)"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md border text-xs sm:text-sm font-medium transition',
                            selectedConcepto === concepto ? 'ring-2 ring-indigo-500 border-indigo-500 text-indigo-500' : 'border-gray-200 dark:border-gray-600',
                            isCorrectConcepto(concepto) ? 'bg-green-500 text-white border-green-500' : '',
                            isIncorrectConcepto(concepto) ? 'bg-red-500 text-white border-red-500' : '',
                            !isCorrectConcepto(concepto) && !isIncorrectConcepto(concepto) ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600' : '',
                            'disabled:opacity-50 disabled:cursor-not-allowed'
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
                            'w-full text-left px-3 py-2 rounded-md border text-xs sm:text-sm font-medium transition',
                            selectedDefinicion === definicion ? 'ring-2 ring-indigo-500 border-indigo-500 text-indigo-500' : 'border-gray-200 dark:border-gray-600',
                            isCorrectDefinicion(definicion) ? 'bg-green-500 text-white border-green-500' : '',
                            isIncorrectDefinicion(definicion) ? 'bg-red-500 text-white border-red-500' : '',
                            !isCorrectDefinicion(definicion) && !isIncorrectDefinicion(definicion)
                                ? (studentMatches.some(p => p.definicion === definicion)
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600')
                                : '',
                            'disabled:opacity-50 disabled:cursor-not-allowed'
                        ]">
                        {{ definicion }}
                    </button>
                </div>
            </div>
        </div>


        <div class="flex flex-col sm:flex-row gap-3">
            <button
                class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                @click="checkAnswer" :disabled="props.showFeedback || validated || !studentMatches.length">
                Comprobar respuesta
            </button>
        </div>
    </div>
</template>
