<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { computed, ref, watch } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useVoiceChat, type VoiceChatProps } from './useVoiceChat/index';

const props = withDefaults(defineProps<VoiceChatProps>(), {
    defaultVoice: 'alloy',
    defaultModel: 'gpt-realtime-mini-2025-10-06',
    sessionDuration: 120,
});

const voiceOptions = [
    { value: 'alloy', label: 'Alloy' },
    { value: 'ash', label: 'Ash' },
    { value: 'coral', label: 'Coral' },
    { value: 'echo', label: 'Echo' },
    { value: 'fable', label: 'Fable' },
    { value: 'onyx', label: 'Onyx' },
    { value: 'nova', label: 'Nova' },
    { value: 'sage', label: 'Sage' },
    { value: 'shimmer', label: 'Shimmer' },
] satisfies Array<{ value: string; label: string }>;

const aiLevels = [
    { value: 'basico', label: 'Básico', description: 'Conversaciones comunes y fáciles de seguir con vocabulario cotidiano.' },
    { value: 'intermedio', label: 'Intermedio', description: 'Temas más concretos con explicaciones breves y preguntas que invitan a detallar.' },
    { value: 'avanzado', label: 'Avanzado', description: 'Conversación elocuente con ideas complejas y matices para profundizar.' },
] satisfies Array<{ value: string; label: string; description: string }>;

const defaultLevel = 'intermedio';

const selectedVoice = ref(
    voiceOptions.find((option) => option.value === props.defaultVoice)?.value ??
    props.defaultVoice ??
    voiceOptions[0].value,
);

const selectedLevel = ref(
    aiLevels.find((option) => option.value === defaultLevel)?.value ?? aiLevels[0].value,
);

const {
    statusMessage,
    isConnecting,
    isActive,
    countdown,
    errorMessage,
    remoteAudioEl,
    startSession,
    stopSession,
    localLevel,
    remoteLevel,
    localSpectrum,
    remoteSpectrum,
    isUserSpeaking,
    isAiSpeaking,
} = useVoiceChat(props);

watch(
    () => props.defaultVoice,
    (voice) => {
        if (!voice) {
            return;
        }

        if (!isActive.value) {
            selectedVoice.value = voice;
        }
    },
);

const selectedVoiceLabel = computed(() => {
    return voiceOptions.find((option) => option.value === selectedVoice.value)?.label ?? selectedVoice.value;
});

const selectedLevelDetails = computed(() => {
    return aiLevels.find((option) => option.value === selectedLevel.value) ?? aiLevels[0];
});

const availableVoices = computed(() => {
    if (!selectedVoice.value) {
        return voiceOptions;
    }

    if (voiceOptions.some((option) => option.value === selectedVoice.value)) {
        return voiceOptions;
    }

    return [
        ...voiceOptions,
        { value: selectedVoice.value, label: selectedVoiceLabel.value ?? selectedVoice.value },
    ];
});

const countdownDisplay = computed(() => countdown.value.toString().padStart(2, '0'));
const canChangeVoice = computed(() => !isActive.value && !isConnecting.value);
const userSpeakingState = computed(() => (isUserSpeaking.value ? 'Hablando' : 'En espera'));
const aiSpeakingState = computed(() => (isAiSpeaking.value ? 'Respondiendo' : 'En espera'));

const startSessionWithVoice = () =>
    startSession({ voice: selectedVoice.value, level: selectedLevel.value });
</script>

<template>

    <Head title="Chat de voz" />

    <StudentLayout>
        <!-- <template #header>
            <PageHeader title="Chat de voz" subtitle="Practica tu inglés con una conversación guiada por IA."
                icon="fa-solid fa-microphone-lines" :breadcrumbs="[
                    { label: 'Inicio', href: route('student.units.index'), icon: 'fa-solid fa-house' },
                    { label: 'Chat de voz' }
                ]" gradient-classes="from-sky-600 to-indigo-600" />
        </template> -->
    <div class="flex min-h-[calc(100vh-56px)] flex-col bg-gray-50 dark:bg-gray-800">
            <div class="w-full flex-1">
                <div class="mx-auto flex h-full w-full max-w-[1400px] flex-col px-4 sm:px-6 lg:px-8 py-10">
                    <div
                        class="flex h-full flex-col gap-8 lg:grid lg:grid-cols-[320px,minmax(0,1fr)] xl:grid-cols-[340px,minmax(0,1fr)]">
                        <aside class="order-2 space-y-6 lg:order-1">
                            <div
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configuración de voz
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Define la voz con la que quieres practicar antes de comenzar la sesión.
                                </p>
                                <label
                                    class="mt-6 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Selecciona la voz
                                </label>
                                <SelectInput v-model="selectedVoice" :disabled="!canChangeVoice" class="mt-2 w-full">
                                    <option v-for="voice in availableVoices" :key="voice.value" :value="voice.value">
                                        {{ voice.label }}
                                    </option>
                                </SelectInput>
                                <div
                                    class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300">
                                    <p class="font-semibold text-gray-700 dark:text-gray-200">Duración de la sesión</p>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">{{ props.sessionDuration }}
                                        segundos</p>
                                </div>
                            </div>

                            <div
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nivel de conversación
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Ajusta la dificultad para que la IA adapte el tono y el vocabulario de la charla.
                                </p>
                                <label
                                    class="mt-6 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Selecciona el nivel
                                </label>
                                <SelectInput v-model="selectedLevel" class="mt-2 w-full">
                                    <option v-for="level in aiLevels" :key="level.value" :value="level.value">
                                        {{ level.label }}
                                    </option>
                                </SelectInput>
                                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ selectedLevelDetails.description }}
                                </p>
                            </div>
                        </aside>

                        <section
                            class="order-1 flex min-h-0 flex-col rounded-3xl border border-gray-100 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800/80 sm:p-8 lg:order-2">
                            <div class="flex min-h-0 flex-col gap-8">

                                <div
                                    class="order-1 rounded-2xl text-left md:order-2">
                                    <div class="flex flex-col gap-6 px-0 py-0">

                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">
                                                    Estado actual
                                                </p>
                                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ statusMessage }}
                                                </p>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                <p
                                                    class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Voz seleccionada
                                                </p>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ selectedVoiceLabel }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Duración {{ props.sessionDuration }}s
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                                            <PrimaryButton type="button" :disabled="isConnecting || isActive" @click="startSessionWithVoice" class="w-full">
                                                <span v-if="isConnecting" class="flex items-center gap-2">
                                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                                    Conectando...
                                                </span>
                                                <span v-else class="flex items-center justify-center gap-2">
                                                    <i class="fa-solid fa-microphone"></i>
                                                    Iniciar sesión
                                                </span>
                                            </PrimaryButton>
                                            <SecondaryButton v-if="isActive" type="button" @click="stopSession('Sesión finalizada por ti.')" class="w-full">
                                                <i class="fa-solid fa-phone-slash mr-2"></i>
                                                Finalizar
                                            </SecondaryButton>
                                        </div>

                                        <div v-if="isActive"
                                            class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3 text-sky-600 shadow-sm dark:bg-gray-900/70 dark:text-sky-300">
                                            <span class="text-xs font-semibold uppercase tracking-wide">Tiempo
                                                restante</span>
                                            <span class="text-2xl font-semibold">00:{{ countdownDisplay }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="errorMessage"
                                    class="order-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-left text-sm text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-200 md:order-3">
                                    {{ errorMessage }}
                                </div>

                                <div class="order-3 grid gap-6 md:order-4 lg:grid-cols-2">
                                    <div
                                        class="rounded-2xl border border-sky-100 bg-sky-50 p-6 dark:border-sky-500/20 dark:bg-sky-500/10">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">
                                                    Tu voz
                                                </p>
                                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ userSpeakingState }}
                                                </p>
                                            </div>
                                            <span :class="[
                                                isUserSpeaking
                                                    ? 'bg-sky-600 text-white'
                                                    : 'bg-white text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
                                                'rounded-full px-3 py-1 text-xs font-semibold transition-colors duration-200'
                                            ]">
                                                {{ localLevel }}%
                                            </span>
                                        </div>
                                        <div
                                            class="mt-4 h-28 overflow-hidden rounded-2xl bg-white/80 p-3 dark:bg-gray-900/70">
                                            <div class="flex h-full items-end gap-1">
                                                <span v-for="(value, index) in localSpectrum" :key="`local-${index}`"
                                                    class="flex-1 rounded-full bg-sky-400/60 transition-all duration-150 ease-out dark:bg-sky-400/80"
                                                    :style="{ height: `${Math.min(100, Math.max(6, value))}%` }" />
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">
                                                    IA
                                                </p>
                                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ aiSpeakingState }}
                                                </p>
                                            </div>
                                            <span :class="[
                                                isAiSpeaking
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-white text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300',
                                                'rounded-full px-3 py-1 text-xs font-semibold transition-colors duration-200'
                                            ]">
                                                {{ remoteLevel }}%
                                            </span>
                                        </div>
                                        <div
                                            class="mt-4 h-28 overflow-hidden rounded-2xl bg-white/80 p-3 dark:bg-gray-900/70">
                                            <div class="flex h-full items-end gap-1">
                                                <span v-for="(value, index) in remoteSpectrum" :key="`remote-${index}`"
                                                    class="flex-1 rounded-full bg-indigo-400/60 transition-all duration-150 ease-out dark:bg-indigo-400/80"
                                                    :style="{ height: `${Math.min(100, Math.max(6, value))}%` }" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <audio ref="remoteAudioEl" autoplay playsinline class="hidden" />
        </div>
    </StudentLayout>
</template>
