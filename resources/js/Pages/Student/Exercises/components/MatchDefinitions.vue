<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    exercise: Object,
    showFeedback: Boolean,
    lastAnswer: Array,
    unitSlug: { type: [String, Number], required: false }
});
const emit = defineEmits(['answered', 'finish']);

const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

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

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    // Atajos numéricos para conceptos
    if (/^[1-6]$/.test(e.key)) {
        const idx = parseInt(e.key, 10) - 1;
        if (selectedConcepto.value === null && conceptos.value[idx]) {
            selectConcepto(conceptos.value[idx]);
            return;
        }
        if (selectedConcepto.value !== null && definiciones.value[idx]) {
            selectDefinicion(definiciones.value[idx]);
            return;
        }
    }
    // Enter para comprobar
    if (e.key === 'Enter' && !validated.value && studentMatches.value.length > 0) {
        checkAnswer();
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
        // Si todas las parejas están hechas, emitir automáticamente el evento answered
        const sol = solutionArray();
        if (studentMatches.value.length === sol.length) {
            const isCorrect = studentMatches.value.every(pair => sol.some(s => s.concepto === pair.concepto && s.definicion === pair.definicion));
            validated.value = true;
            emit('answered', isCorrect, JSON.parse(JSON.stringify(studentMatches.value)));
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
    <div class="flex flex-col gap-6 w-full">
        <div v-if="conceptos.length && definiciones.length" class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
            <!-- Conceptos -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="(concepto, idx) in conceptos" :key="concepto" @click="selectConcepto(concepto)"
                        :disabled="props.showFeedback || validated || studentMatches.some(p => p.concepto === concepto)"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            (isCorrectConcepto(concepto)) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            (isIncorrectConcepto(concepto)) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !isCorrectConcepto(concepto) && !isIncorrectConcepto(concepto) && selectedConcepto === concepto ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !isCorrectConcepto(concepto) && !isIncorrectConcepto(concepto) && studentMatches.some(p => p.concepto === concepto) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !isCorrectConcepto(concepto) && !isIncorrectConcepto(concepto) && !studentMatches.some(p => p.concepto === concepto) && selectedConcepto !== concepto ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        <span v-if="windowWidth >= 768"
                            class="inline-block mr-2 px-2 py-1 rounded border border-blue-400/30 text-sm font-semibold text-gray-300 bg-transparent select-none"
                            style="min-width:2.2rem;text-align:center;">{{ idx + 1 }}</span>
                        {{ concepto }}
                    </button>
                </div>
            </div>
            <!-- Definiciones -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="(definicion, idx) in definiciones" :key="definicion"
                        @click="selectDefinicion(definicion)"
                        :disabled="props.showFeedback || validated || studentMatches.some(p => p.definicion === definicion)"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            (isCorrectDefinicion(definicion)) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            (isIncorrectDefinicion(definicion)) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !isCorrectDefinicion(definicion) && !isIncorrectDefinicion(definicion) && selectedDefinicion === definicion ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !isCorrectDefinicion(definicion) && !isIncorrectDefinicion(definicion) && studentMatches.some(p => p.definicion === definicion) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !isCorrectDefinicion(definicion) && !isIncorrectDefinicion(definicion) && !studentMatches.some(p => p.definicion === definicion) && selectedDefinicion !== definicion ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        <span v-if="windowWidth >= 768"
                            class="inline-block mr-2 px-2 py-1 rounded border border-blue-400/30 text-sm font-semibold text-gray-300 bg-transparent select-none"
                            style="min-width:2.2rem;text-align:center;">
                            {{ idx + 1 }}
                        </span>
                        {{ definicion }}
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-4 w-full">
            <!-- Botón Terminar -->
            <PrimaryButton @click="handleFinish" class="hidden md:flex w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </PrimaryButton>

            <div class="flex-1"></div>

            <!-- Botón Comprobar -->
            <PrimaryButton @click="checkAnswer" :disabled="props.showFeedback || validated || !studentMatches.length"
                :class="[
                    'w-full md:w-1/3 flex items-center',
                    (props.showFeedback || validated || !studentMatches.length) ? 'opacity-60 cursor-not-allowed' : ''
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
