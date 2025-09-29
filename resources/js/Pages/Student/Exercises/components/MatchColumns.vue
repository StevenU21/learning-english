<script setup>
import { ref, watch } from 'vue';
// Nueva lógica solicitada para Match Columns: selección de pares con comprobación única.
// Se mantiene el contrato de emitir 'answered' (correcto, respuestaClonada).
const props = defineProps({
    exercise: Object,
    showFeedback: Boolean,
    lastAnswer: Array
});
const emit = defineEmits(['answered']);

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
const checked = ref(false);     // intento único

watch(() => props.exercise, () => {
    leftValues.value = getLeftValues();
    rightValues.value = getRightValues().sort(() => Math.random() - 0.5);
    selectedLeft.value = null;
    selectedRight.value = null;
    studentMatches.value = [];
    checked.value = false;
}, { immediate: true });

function solutionArray() {
    return Array.isArray(props.exercise?.solution)
        ? props.exercise.solution
        : props.exercise?.solution ? [props.exercise.solution] : [];
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
        selectedLeft.value = null;
        selectedRight.value = null;
    }
}

function isPairCorrect(pair) {
    const sol = solutionArray();
    return sol.some(s => s.left === pair.left && s.right === pair.right);
}

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
    <div class="space-y-4">
        <div v-if="leftValues.length && rightValues.length" class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
            <!-- Columna Izquierda -->
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-600 dark:text-gray-300 text-[11px] uppercase tracking-wide">Izquierda</h4>
                <div class="space-y-2">
                    <button
                        v-for="left in leftValues"
                        :key="left"
                        @click="selectLeft(left)"
                        :disabled="props.showFeedback || checked"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md border text-xs sm:text-sm font-medium transition',
                            selectedLeft === left ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600',
                            studentMatches.some(p => p.left === left) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'
                        ]"
                    >
                        {{ left }}
                    </button>
                </div>
            </div>
            <!-- Columna Derecha -->
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-600 dark:text-gray-300 text-[11px] uppercase tracking-wide">Derecha</h4>
                <div class="space-y-2">
                    <button
                        v-for="right in rightValues"
                        :key="right"
                        @click="selectRight(right)"
                        :disabled="props.showFeedback || checked"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md border text-xs sm:text-sm font-medium transition',
                            selectedRight === right ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600',
                            studentMatches.some(p => p.right === right) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'
                        ]"
                    >
                        {{ right }}
                    </button>
                </div>
            </div>
        </div>


        <div class="flex flex-col sm:flex-row gap-3">
            <button
                class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                @click="checkAnswer"
                :disabled="props.showFeedback || checked || !studentMatches.length"
            >
                Comprobar respuesta
            </button>
        </div>
    </div>
</template>
