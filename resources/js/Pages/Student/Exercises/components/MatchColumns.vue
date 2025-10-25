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

function getLeftValues() {
    return Array.isArray(props.exercise?.options)
        ? [...new Set(props.exercise.options.map(opt => opt.left).filter(v => v))]
        : [];
}
function getRightValues() {
    return Array.isArray(props.exercise?.options)
        ? [...new Set(props.exercise.options.map(opt => opt.right).filter(v => v))]
        : [];
}

const leftValues = ref(getLeftValues());
const rightValues = ref(getRightValues().sort(() => Math.random() - 0.5));
const selectedLeft = ref(null);
const selectedRight = ref(null);
const studentMatches = ref([]); // [{left, right}]
const feedback = ref({});       // { left: true/false }
const checked = ref(false);     // intento único

watch(() => props.exercise, () => {
    leftValues.value = getLeftValues();
    rightValues.value = getRightValues().sort(() => Math.random() - 0.5);
    selectedLeft.value = null;
    selectedRight.value = null;
    studentMatches.value = [];
    feedback.value = {};
    checked.value = false;
}, { immediate: true });

function solutionArray() {
    return Array.isArray(props.exercise?.solution)
        ? props.exercise.solution
        : props.exercise?.solution ? [props.exercise.solution] : [];
}

function validatePair(left, right) {
    return solutionArray().some(sol => sol.left === left && sol.right === right);
}

function selectLeft(left) {
    if (props.showFeedback || checked.value) return;
    selectedLeft.value = left;
}
function selectRight(right) {
    if (props.showFeedback || checked.value) return;
    selectedRight.value = right;
    if (selectedLeft.value && selectedRight.value) {
        const idx = studentMatches.value.findIndex(p => p.left === selectedLeft.value);
        if (idx !== -1) {
            studentMatches.value[idx].right = selectedRight.value;
        } else {
            studentMatches.value.push({ left: selectedLeft.value, right: selectedRight.value });
        }
        feedback.value[selectedLeft.value] = validatePair(selectedLeft.value, selectedRight.value);
        // Si la pareja es incorrecta, validar y emitir respuesta incorrecta
        if (feedback.value[selectedLeft.value] === false && !checked.value) {
            checked.value = true;
            emit('answered', false, JSON.parse(JSON.stringify(studentMatches.value)));
        }
        // Si todas las parejas están hechas, emitir automáticamente el evento answered
        const sol = solutionArray();
        if (studentMatches.value.length === sol.length) {
            const isCorrect = studentMatches.value.every(pair => sol.some(s => s.left === pair.left && s.right === pair.right));
            checked.value = true;
            emit('answered', isCorrect, JSON.parse(JSON.stringify(studentMatches.value)));
        }
        selectedLeft.value = null;
        selectedRight.value = null;
    }
}

function isCorrectLeft(left) { return feedback.value[left] === true; }
function isIncorrectLeft(left) { return feedback.value[left] === false; }
function isCorrectRight(right) {
    const pair = studentMatches.value.find(p => p.right === right);
    return pair ? feedback.value[pair.left] === true : false;
}
function isIncorrectRight(right) {
    const pair = studentMatches.value.find(p => p.right === right);
    return pair ? feedback.value[pair.left] === false : false;
}

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    // Atajos numéricos para izquierda/derecha
    if (/^[1-6]$/.test(e.key)) {
        const idx = parseInt(e.key, 10) - 1;
        if (selectedLeft.value === null && leftValues.value[idx]) {
            selectLeft(leftValues.value[idx]);
            return;
        }
        if (selectedLeft.value !== null && rightValues.value[idx]) {
            selectRight(rightValues.value[idx]);
            return;
        }
    }
    // Enter para comprobar
    if (e.key === 'Enter' && !checked.value && studentMatches.value.length > 0) {
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

function checkAnswer() {
    if (checked.value) return;
    const sol = solutionArray();
    const isCorrect = studentMatches.value.every(pair => sol.some(s => s.left === pair.left && s.right === pair.right))
        && studentMatches.value.length === sol.length;
    checked.value = true;
    emit('answered', isCorrect, JSON.parse(JSON.stringify(studentMatches.value)));
}
</script>

<template>
    <div class="flex flex-col gap-6 w-full">
        <div v-if="leftValues.length && rightValues.length" class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
            <!-- Columna Izquierda -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="(left, idx) in leftValues" :key="left" @click="selectLeft(left)"
                        :disabled="props.showFeedback || checked || studentMatches.some(p => p.left === left)" :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            (isCorrectLeft(left)) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            (isIncorrectLeft(left)) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !isCorrectLeft(left) && !isIncorrectLeft(left) && selectedLeft === left ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !isCorrectLeft(left) && !isIncorrectLeft(left) && studentMatches.some(p => p.left === left) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !isCorrectLeft(left) && !isIncorrectLeft(left) && !studentMatches.some(p => p.left === left) && selectedLeft !== left ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        <span v-if="windowWidth >= 768"
                            class="inline-block mr-2 px-2 py-1 rounded border border-blue-400/30 text-sm font-semibold text-gray-300 bg-transparent select-none"
                            style="min-width:2.2rem;text-align:center;">{{ idx + 1 }}</span>
                        {{ left }}
                    </button>
                </div>
            </div>
            <!-- Columna Derecha -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <button v-for="(right, idx) in rightValues" :key="right" @click="selectRight(right)"
                        :disabled="props.showFeedback || checked || studentMatches.some(p => p.right === right)" :class="[
                            'w-full text-left px-3 py-2 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                            (isCorrectRight(right)) ? 'bg-gray-800 border-green-500 text-green-400' : '',
                            (isIncorrectRight(right)) ? 'bg-gray-800 border-red-500 text-red-400' : '',
                            !isCorrectRight(right) && !isIncorrectRight(right) && selectedRight === right ? 'bg-gray-800 border-blue-400 text-blue-400' : '',
                            !isCorrectRight(right) && !isIncorrectRight(right) && studentMatches.some(p => p.right === right) ? 'bg-gray-800/80 border-blue-400/30 text-white' : '',
                            !isCorrectRight(right) && !isIncorrectRight(right) && !studentMatches.some(p => p.right === right) && selectedRight !== right ? 'bg-gray-800/80 border-blue-400/30 text-white' : ''
                        ]">
                        <span v-if="windowWidth >= 768"
                            class="inline-block mr-2 px-2 py-1 rounded border border-blue-400/30 text-sm font-semibold text-gray-300 bg-transparent select-none"
                            style="min-width:2.2rem;text-align:center;">
                            {{ idx + 1 }}
                        </span>
                        {{ right }}
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
            <PrimaryButton @click="checkAnswer" :disabled="props.showFeedback || checked || !studentMatches.length"
                :class="[
                    'w-full md:w-1/3 flex items-center',
                    (props.showFeedback || checked || !studentMatches.length) ? 'opacity-60 cursor-not-allowed' : ''
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
