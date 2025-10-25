<script setup>
import ExerciseResolverLayout from '@/Layouts/ExerciseResolverLayout.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import MultipleChoice from './components/MultipleChoice.vue';
import TrueFalse from './components/TrueFalse.vue';
import FillBlank from './components/FillBlank.vue';
import OrderElements from './components/OrderElements.vue';
import MatchColumns from './components/MatchColumns.vue';
import MatchDefinitions from './components/MatchDefinitions.vue';
import CompleteDialog from './components/CompleteDialog.vue';
import ChooseAudio from './components/ChooseAudio.vue';
import ListenAndAnswer from './components/ListenAndAnswer.vue';
import ListenAndWrite from './components/ListenAndWrite.vue';
import TranslateSentence from './components/TranslateSentence.vue';
import SayThePhrase from './components/SayThePhrase.vue';
import Badge from '@/Components/Badge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
// Track currently playing feedback audios so we can stop them on navigation
const activeAudios = ref([]);

const total = computed(() => props.exercises.length);

function playSound(type) {
    const srcMap = {
        success: '/sounds/success02.wav',
        error: '/sounds/error.mp3',
        finish: '/sounds/finish02.wav'
    };
    const src = type === 'error' ? srcMap.error : type === 'finish' ? srcMap.finish : srcMap.success;
    const audio = new Audio(src);
    // When the audio ends, remove it from the tracking list
    audio.addEventListener('ended', () => {
        const idx = activeAudios.value.indexOf(audio);
        if (idx !== -1) activeAudios.value.splice(idx, 1);
    });
    activeAudios.value.push(audio);
    audio.play();
}

function stopAllSounds() {
    // Pause and reset any feedback audio we started
    activeAudios.value.forEach(a => {
        try {
            a.pause();
            a.currentTime = 0;
        } catch (_) { /* no-op */ }
    });
    activeAudios.value.length = 0;
}

function handleAnswer(result, userValue = null) {
    answered.value[current.value] = result;
    userAnswers.value[current.value] = userValue;
    lastAnswer.value = userValue;
    showFeedback.value = true;
    playSound(result ? 'success' : 'error');
}

function nextExercise() {
    // Stop any ongoing feedback sounds (success/error) before moving on
    stopAllSounds();
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
    router.get(route('student.units.lessons.index', { unit: props.lesson.unit?.slug || props.lesson.unit_slug || props.lesson.unit_id }));
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
    // If the finish sound is playing on the summary screen, stop it when finalizing
    stopAllSounds();
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
    'Completar diálogo': CompleteDialog,
    'Elige lo que escuchas': ChooseAudio,
    'Escucha y responde': ListenAndAnswer,
    'Escucha y escribe': ListenAndWrite,
    'Traduce la oración': TranslateSentence,
    'Di la frase': SayThePhrase
};

// Listener para avanzar con barra espaciadora (Space)
function handleNextKey(e) {
    if (
        showFeedback.value &&
        answered.value[current.value] !== undefined &&
        !showSummary.value &&
        !finished.value &&
        (e.code === 'Space' || e.key === ' ')
    ) {
        e.preventDefault();
        nextExercise();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleNextKey);
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleNextKey);
});
</script>

<template>
    <ExerciseResolverLayout :progress="Math.round((answered.filter(a => a !== undefined).length / total) * 100)">
        <template #progress>
            <ProgressBar :value="Math.round((answered.filter(a => a !== undefined).length / total) * 100)" />
        </template>

        <Head :title="`Ejercicios - ${lesson.name}`" />
        <div class="flex flex-col items-center justify-center min-h-[70vh] w-full">
            <div class="w-full sm:w-[40vw] max-w-2xl mx-auto">
                <!-- Active Exercise -->
                <div v-if="!finished && !showSummary" class="space-y-4 px-3">
                    <div class="text-center text-xs text-gray-400 mb-2">
                        Ejercicio {{ current + 1 }} / {{ total }}
                    </div>
                    <h3 class="font-bold text-white mb-2 text-sm uppercase tracking-wide">
                        {{ exercises[current].exercise_type?.name }}
                    </h3>
                    <p class="text-gray-200 text-base mb-4 leading-relaxed">
                        {{ exercises[current].prompt }}
                    </p>
                    <component :is="componentMap[exercises[current].exercise_type?.name]" :exercise="exercises[current]"
                        :unitSlug="lesson.unit?.slug || lesson.unit_slug || lesson.unit_id"
                        :showFeedback="showFeedback" :lastAnswer="lastAnswer" @answered="handleAnswer" />
                    <div class="mt-5 flex flex-col gap-3 text-center">
                        <PrimaryButton v-if="showFeedback && current < total - 1" @click="nextExercise" class="w-full">
                            <i class="fa-solid fa-keyboard mr-2"></i> Siguiente
                        </PrimaryButton>
                        <PrimaryButton v-else-if="showFeedback && current === total - 1" @click="nextExercise"
                            class="w-full">
                            <i class="fa-solid fa-keyboard mr-2"></i> Ver resumen
                        </PrimaryButton>
                    </div>
                </div>
                <!-- Summary -->
                <div v-else-if="showSummary" class="space-y-6 text-white w-full sm:w-[40vw] max-w-2xl mx-auto px-3">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-gray-400"></i>
                        Resumen de tus respuestas
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div v-for="(exercise, idx) in exercises" :key="exercise.id"
                            class="flex flex-col gap-1 border-b border-[#26313a] pb-2 last:border-0">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">{{ idx + 1 }}. {{ exercise.prompt }}</span>
                                <Badge :type="answered[idx] ? 'success' : 'error'">
                                    {{ answered[idx] ? 'Correcto' : 'Incorrecto' }}
                                </Badge>
                            </div>
                            <div class="text-xs text-gray-400">Tu respuesta: {{ formatAnswer(userAnswers[idx]) }}</div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3">
                        <PrimaryButton @click="saveSummary" :disabled="saving" class="w-full">
                            <i class="fa-solid fa-flag-checkered mr-2"></i> Finalizar
                        </PrimaryButton>
                    </div>
                </div>
                <!-- Finished -->
                <div v-else class="text-center space-y-6 text-white w-full sm:w-[40vw] max-w-2xl mx-auto">
                    <h3 class="text-xl font-bold">¡Has terminado la ronda de ejercicios!</h3>
                    <div class="flex flex-col gap-3 w-full mx-auto">
                        <button @click="goToLessons"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 w-full">Volver
                            a lecciones</button>
                        <button @click="goToUnits"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 w-full">Unidades</button>
                    </div>
                </div>
            </div>
        </div>
    </ExerciseResolverLayout>
</template>
