<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MultipleChoice from './components/MultipleChoice.vue';
import TrueFalse from './components/TrueFalse.vue';
import FillBlank from './components/FillBlank.vue';
import OrderElements from './components/OrderElements.vue';
import MatchColumns from './components/MatchColumns.vue';
import MatchDefinitions from './components/MatchDefinitions.vue';
import CompleteDialog from './components/CompleteDialog.vue';

const props = defineProps({
    lesson: { type: Object, required: true },
    exercises: { type: Array, required: true }
});

const current = ref(0);
const answered = ref([]); // boolean results per exercise
const userAnswers = ref([]); // raw user answers per exercise
const showFeedback = ref(false);
const finished = ref(false);
const showSummary = ref(false);
const lastAnswer = ref(null);
const saving = ref(false);

const total = computed(() => props.exercises.length);

function playSound(type) {
    let src = '/sounds/success.mp3';
    if (type === 'error') src = '/sounds/error.mp3';
    else if (type === 'finish') src = '/sounds/finish.mp3';
    const audio = new Audio(src);
    audio.play();
}

function handleAnswer(result, userValue = null) {
    answered.value[current.value] = result;
    userAnswers.value[current.value] = userValue;
    lastAnswer.value = userValue;
    showFeedback.value = true;
    playSound(result ? 'success' : 'error');
}

function nextExercise() {
    showFeedback.value = false;
    lastAnswer.value = null;
    if (current.value < total.value - 1) {
        current.value++;
    } else {
        showSummary.value = true;
        playSound('finish');
    }
}

function retryCurrent() {
    showFeedback.value = false;
    lastAnswer.value = null;
    // keep current index, allow user to answer again
}

function goToUnits() {
    router.get(route('student.units.index'));
}

function goToLessons() {
    router.get(route('student.units.start', props.lesson.unit_id));
}

function formatAnswer(answer) {
    if (answer === undefined || answer === null || answer === '') return 'Sin respuesta';
    // Arrays
    if (Array.isArray(answer)) {
        if (!answer.length) return 'Sin respuesta';
        // If array of primitives
        if (answer.every(a => ['string', 'number', 'boolean'].includes(typeof a))) {
            return answer.join(', ');
        }
        // Array of objects (pairs)
        if (answer.every(a => a && typeof a === 'object')) {
            return answer.map(a => {
                if ('left' in a && 'right' in a) return `${a.left} → ${a.right}`;
                if ('concepto' in a && 'definicion' in a) return `${a.concepto} → ${a.definicion}`;
                // Fallback: first two key-value pairs
                const entries = Object.entries(a).slice(0, 2).map(([k, v]) => `${k}: ${v}`);
                return entries.join(' | ');
            }).join(', ');
        }
        return 'Sin respuesta';
    }
    // Single object (maybe one pair)
    if (typeof answer === 'object') {
        if ('left' in answer && 'right' in answer) return `${answer.left} → ${answer.right}`;
        if ('concepto' in answer && 'definicion' in answer) return `${answer.concepto} → ${answer.definicion}`;
        return JSON.stringify(answer);
    }
    return String(answer);
}

async function saveSummary() {
    saving.value = true;
    const attempts = props.exercises.map((exercise, idx) => ({
        exercise_id: exercise.id,
        lesson_id: props.lesson.id,
        unit_id: props.lesson.unit_id,
        answer_given: userAnswers.value[idx],
        is_correct: answered.value[idx] === true,
        attempt_number: 1
    }));
    await router.post(route('student.exercises.attemptsBatch'), { attempts }, {
        onSuccess: () => {
            finished.value = true;
            showSummary.value = false;
        },
        onFinish: () => saving.value = false
    });
}

const componentMap = {
    'Opción múltiple': MultipleChoice,
    'Verdadero o falso': TrueFalse,
    'Completar espacios': FillBlank,
    'Ordenar elementos': OrderElements,
    'Relacionar columnas': MatchColumns,
    'Emparejar definiciones': MatchDefinitions,
    'Completar diálogo': CompleteDialog
};

</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`Ejercicios - ${lesson.name}`" />
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <i class="fa-solid fa-pencil text-gray-400"></i>
                    Ejercicios de {{ lesson.name }}
                </h2>
                <div class="flex gap-2">
                    <button @click="goToLessons"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Lecciones
                    </button>
                    <button @click="goToUnits"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                        <i class="fa-solid fa-layer-group mr-2"></i>Unidades
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Active Exercise -->
                <div v-if="!finished && !showSummary" class="space-y-4">
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Ejercicio {{ current + 1 }} / {{ total }}</span>
                        <span v-if="answered[current] !== undefined"
                            :class="answered[current] ? 'text-green-500' : 'text-red-500'">
                            {{ answered[current] ? 'Correcto' : 'Incorrecto' }}
                        </span>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2 text-sm uppercase tracking-wide">
                            {{ exercises[current].exercise_type?.name }}
                        </h3>
                        <p class="text-gray-700 dark:text-gray-300 text-base mb-4 leading-relaxed">
                            {{ exercises[current].prompt }}
                        </p>

                        <component :is="componentMap[exercises[current].exercise_type?.name]"
                            :exercise="exercises[current]" :showFeedback="showFeedback" :lastAnswer="lastAnswer"
                            @answered="handleAnswer" />

                        <div class="mt-5 flex flex-col sm:flex-row gap-3">
                            <button v-if="showFeedback && current < total - 1" @click="nextExercise"
                                class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Siguiente
                            </button>
                            <button v-else-if="showFeedback && current === total - 1" @click="nextExercise"
                                class="flex-1 inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Ver resumen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div v-else-if="showSummary"
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-gray-400"></i>
                        Resumen de tus respuestas
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div v-for="(exercise, idx) in exercises" :key="exercise.id"
                            class="flex flex-col gap-1 border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ idx + 1 }}. {{
                                    exercise.prompt
                                    }}</span>
                                <span :class="answered[idx] ? 'text-green-500' : 'text-red-500'">
                                    {{ answered[idx] ? 'Correcto' : 'Incorrecto' }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tu respuesta: {{
                                formatAnswer(userAnswers[idx]) }}</div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button @click="saveSummary" :disabled="saving"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Finalizar</button>
                        <button @click="goToLessons"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">Lecciones</button>
                    </div>
                </div>

                <!-- Finished -->
                <div v-else class="text-center space-y-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">¡Has terminado la ronda de
                        ejercicios!
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-3 max-w-sm mx-auto">
                        <button @click="goToLessons"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Volver
                            a lecciones</button>
                        <button @click="goToUnits"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">Unidades</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
    