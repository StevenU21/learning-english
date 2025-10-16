<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch, type ComponentPublicInstance } from 'vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextArea from '@/Components/TextArea.vue';
import { useTextChat, type TextChatProps } from './useTextChat';

const props = withDefaults(defineProps<TextChatProps>(), {
    defaultLevel: 'intermedio',
    starterPrompts: () => [],
});

const {
    messages,
    draftMessage,
    isSending,
    errorMessage,
    selectedLevel,
    followUpSuggestions,
    latestVocabulary,
    latestGrammarTips,
    sendMessage,
    resetChat,
    applySuggestion,
} = useTextChat(props);

const selectedLevelDetails = computed(() => {
    return (
        props.levels.find((option) => option.value === selectedLevel.value) ??
        props.levels[0] ?? {
            value: selectedLevel.value,
            label: 'Nivel',
            description: 'Selecciona un nivel para recibir orientación adaptada.',
        }
    );
});

const canSendMessage = computed(() => draftMessage.value.trim().length > 0 && !isSending.value);

const chatContainer = ref<HTMLDivElement | null>(null);
const messageInput = ref<ComponentPublicInstance | null>(null);
const resizeObserver = ref<ResizeObserver | null>(null);
const shouldAutoScroll = ref(true);

const lastMessageSignature = computed(() => {
    const lastMessage = messages.value[messages.value.length - 1];

    if (!lastMessage) {
        return '';
    }

    const vocabularySignature = (lastMessage.vocabulary ?? [])
        .map((item) => `${item.term}:${item.definition}:${item.example ?? ''}`)
        .join('|');

    const grammarSignature = (lastMessage.grammarTips ?? []).join('|');
    const followUpSignature = (lastMessage.followUpQuestions ?? []).join('|');

    return [
        lastMessage.id,
        lastMessage.role,
        lastMessage.content,
        lastMessage.streaming ? 'streaming' : 'done',
        vocabularySignature,
        grammarSignature,
        followUpSignature,
    ].join('::');
});

function focusInput() {
    nextTick(() => {
        const element = messageInput.value?.$el as HTMLTextAreaElement | undefined;

        if (!element) {
            return;
        }

        try {
            element.focus({ preventScroll: true });
        } catch {
            element.focus();
        }
    });
}

function scrollToBottom(force = false) {
    const container = chatContainer.value;

    if (!container) {
        return;
    }

    if (!force && !shouldAutoScroll.value) {
        return;
    }

    requestAnimationFrame(() => {
        container.scrollTop = container.scrollHeight;
        requestAnimationFrame(() => {
            container.scrollTop = container.scrollHeight;
        });
    });
}

function handleChatScroll() {
    const container = chatContainer.value;

    if (!container) {
        return;
    }

    const distanceFromBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
    shouldAutoScroll.value = distanceFromBottom <= 120;
}

onMounted(() => {
    nextTick(() => {
        scrollToBottom();
    });

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => {
            const container = chatContainer.value;

            if (!container) {
                return;
            }

            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight;
            });
        });

        resizeObserver.value = observer;

        nextTick(() => {
            const element = chatContainer.value;
            if (element) {
                observer.observe(element);
            }
        });
    }

    nextTick(() => {
        handleChatScroll();
    });
});

watch(
    chatContainer,
    (element, previous) => {
        const observer = resizeObserver.value;

        if (!observer) {
            return;
        }

        if (previous) {
            observer.unobserve(previous);
        }

        if (element) {
            observer.observe(element);
        }
    },
);

onBeforeUnmount(() => {
    if (resizeObserver.value) {
        resizeObserver.value.disconnect();
        resizeObserver.value = null;
    }
});

watch(
    lastMessageSignature,
    async () => {
        await nextTick();
        scrollToBottom();
    },
    { flush: 'post' },
);

watch(
    () => {
        const last = messages.value[messages.value.length - 1];

        if (!last) {
            return '';
        }

        return `${last.id}::${last.streaming ? 'streaming' : 'done'}`;
    },
    async (currentState, previousState) => {
        if (!previousState || !currentState) {
            return;
        }

        const [currentId, currentFlag] = currentState.split('::');
        const [previousId, previousFlag] = previousState.split('::');

        if (currentId === previousId && previousFlag === 'streaming' && currentFlag === 'done') {
            await nextTick();
            scrollToBottom();
        }
    },
    { flush: 'post' },
);

async function handleSubmit() {
    shouldAutoScroll.value = true;
    await sendMessage();
    focusInput();
    scrollToBottom(true);
}

function handleStarterPrompt(prompt: string) {
    applySuggestion(prompt);
    focusInput();
}

async function handleResetChat() {
    shouldAutoScroll.value = true;
    await resetChat();
    await nextTick();
    scrollToBottom(true);
}
</script>

<template>

    <Head title="Chat de texto" />

    <StudentLayout>
        <template #header>
            <PageHeader title="Chat de texto"
                subtitle="Practica la escritura y comprensión en inglés con una IA que corrige y sugiere nuevas ideas."
                icon="fa-solid fa-comments" :breadcrumbs="[
                    { label: 'Inicio', href: route('student.units.index'), icon: 'fa-solid fa-house' },
                    { label: 'Chat de texto' }
                ]" gradient-classes="from-violet-600 to-indigo-600" />
        </template>

        <div
            class="flex min-h-[calc(100dvh-56px-64px)] flex-col bg-gray-50 pb-[calc(env(safe-area-inset-bottom)+8px)] dark:bg-gray-800 sm:min-h-[calc(100vh-56px)] sm:pb-8 lg:h-auto lg:min-h-0">
            <div class="w-full flex-1 lg:h-auto">
                <div
                    class="mx-auto flex h-full w-full max-w-[1400px] flex-col px-3 py-2 sm:px-6 sm:py-10 lg:h-auto lg:px-8">
                    <div
                        class="flex h-full flex-col gap-6 lg:grid lg:grid-cols-[320px,minmax(0,1fr)] lg:gap-8 xl:grid-cols-[340px,minmax(0,1fr)]">
                        <aside class="order-2 space-y-6 lg:order-1">
                            <div
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nivel de conversación
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Ajusta el tipo de retroalimentación que recibirás según tu nivel actual.
                                </p>
                                <label
                                    class="mt-6 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Selecciona el nivel
                                </label>
                                <SelectInput v-model="selectedLevel" class="mt-2 w-full">
                                    <option v-for="level in props.levels" :key="level.value" :value="level.value">
                                        {{ level.label }}
                                    </option>
                                </SelectInput>
                                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ selectedLevelDetails.description }}
                                </p>
                            </div>

                            <div
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <p
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Ideas para iniciar</p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Usa un prompt sugerido y personalízalo para tu contexto.
                                </p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button v-for="prompt in props.starterPrompts" :key="prompt" type="button"
                                        @click="handleStarterPrompt(prompt)"
                                        class="rounded-2xl border border-violet-100 bg-violet-50 px-3 py-2 text-left text-xs font-medium text-violet-700 transition hover:border-violet-200 hover:bg-violet-100 hover:text-violet-900 focus:outline-none focus:ring-2 focus:ring-violet-400 dark:border-violet-500/40 dark:bg-violet-500/10 dark:text-violet-200 dark:hover:border-violet-400 dark:hover:bg-violet-500/30 dark:hover:text-white">
                                        {{ prompt }}
                                    </button>
                                </div>
                            </div>

                            <div
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <p
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Estrategias</p>
                                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                                    <li class="flex items-start gap-2">
                                        <i
                                            class="fa-solid fa-pen-to-square mt-0.5 text-indigo-500 dark:text-indigo-300"></i>
                                        Escribe respuestas completas y cuéntale a la IA detalles para obtener
                                        correcciones
                                        específicas.
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i
                                            class="fa-solid fa-lightbulb mt-0.5 text-indigo-500 dark:text-indigo-300"></i>
                                        Pregunta por sinónimos o frases alternativas cuando aprendas una palabra nueva.
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fa-solid fa-book mt-0.5 text-indigo-500 dark:text-indigo-300"></i>
                                        Guarda el vocabulario recomendado y utilízalo en tu siguiente respuesta para
                                        reforzarlo.
                                    </li>
                                </ul>
                            </div>

                            <div v-if="latestVocabulary.length || latestGrammarTips.length"
                                class="hidden rounded-3xl border border-gray-100 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800/80 md:block">
                                <p
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Aprendizaje destacado
                                </p>
                                <div v-if="latestVocabulary.length" class="mt-4 space-y-3">
                                    <p class="text-xs font-semibold uppercase text-indigo-500 dark:text-indigo-300">
                                        Vocabulario
                                    </p>
                                    <div v-for="word in latestVocabulary" :key="word.term"
                                        class="rounded-2xl border border-indigo-100 bg-indigo-50 p-3 text-sm text-indigo-900 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-100">
                                        <div class="font-semibold">{{ word.term }}</div>
                                        <p class="mt-1 text-sm">{{ word.definition }}</p>
                                        <p v-if="word.example"
                                            class="mt-1 text-xs text-indigo-700/80 dark:text-indigo-200/80">
                                            Ejemplo: {{ word.example }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="latestGrammarTips.length" class="mt-4 space-y-2">
                                    <p class="text-xs font-semibold uppercase text-emerald-500 dark:text-emerald-300">
                                        Consejos gramaticales</p>
                                    <div v-for="tip in latestGrammarTips" :key="tip"
                                        class="rounded-2xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-900 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100">
                                        {{ tip }}
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <section
                            class="order-1 flex min-h-0 flex-1 flex-col rounded-3xl border border-gray-100 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-800/80 sm:p-6 lg:order-2 lg:flex-none lg:p-8">
                            <div class="flex h-full min-h-0 flex-col gap-8 lg:flex-none">
                                <div
                                    class="order-1 flex min-h-0 flex-1 flex-col rounded-2xl border border-violet-100 bg-violet-50 p-6 text-left dark:border-violet-500/20 dark:bg-violet-500/10 md:order-2 lg:flex-none">
                                    <div class="flex flex-1 min-h-0 flex-col gap-6 lg:flex-none">
                                        <div ref="chatContainer"
                                            class="flex-none h-[55vh] max-h-[560px] space-y-6 overflow-y-auto pr-0 md:h-[520px] md:max-h-none md:pr-2 lg:h-[560px]"
                                            @scroll="handleChatScroll">
                                            <div v-for="message in messages" :key="message.id"
                                                :class="['flex', message.role === 'user' ? 'justify-end' : 'justify-start']">
                                                <div :class="[
                                                    'max-w-2xl rounded-3xl px-5 py-4 shadow-sm transition',
                                                    message.role === 'user'
                                                        ? 'rounded-br-sm bg-indigo-600 text-white'
                                                        : 'rounded-bl-sm bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100'
                                                ]">
                                                    <div class="text-xs font-semibold uppercase tracking-wide"
                                                        :class="message.role === 'user' ? 'text-indigo-200' : 'text-indigo-500 dark:text-indigo-300'">
                                                        {{ message.role === 'user' ? 'Tú' : 'Nativo' }}
                                                    </div>
                                                    <p v-if="message.content"
                                                        class="mt-2 whitespace-pre-line text-sm leading-relaxed">
                                                        {{ message.content }}
                                                    </p>

                                                    <div v-if="message.streaming && !message.content"
                                                        class="mt-2 flex items-center gap-2 text-xs font-medium text-indigo-400 dark:text-indigo-300">
                                                        <i class="fa-solid fa-circle-notch animate-spin"></i>
                                                        <span>Nativo está escribiendo...</span>
                                                    </div>

                                                    <div v-if="message.vocabulary?.length"
                                                        class="mt-4 space-y-2 rounded-2xl border border-indigo-100 bg-indigo-50 p-3 text-xs text-indigo-900 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-100">
                                                        <p class="font-semibold uppercase text-[10px] tracking-wide">
                                                            Vocabulario recomendado</p>
                                                        <div v-for="item in message.vocabulary" :key="item.term">
                                                            <span class="font-semibold">{{ item.term }}</span> - {{
                                                                item.definition }}
                                                            <div v-if="item.example"
                                                                class="mt-1 text-[11px] opacity-80">
                                                                Example: {{ item.example }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div v-if="message.grammarTips?.length"
                                                        class="mt-3 space-y-2 rounded-2xl border border-emerald-100 bg-emerald-50 p-3 text-xs text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                                                        <p class="font-semibold uppercase text-[10px] tracking-wide">
                                                            Correcciones</p>
                                                        <ul class="list-disc pl-4">
                                                            <li v-for="tip in message.grammarTips" :key="tip">{{ tip }}
                                                            </li>
                                                        </ul>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="h-px w-px" aria-hidden="true"></div>
                                        </div>

                                        <div v-if="followUpSuggestions.length"
                                            class="flex flex-wrap items-center gap-2 rounded-2xl border border-indigo-100 bg-white p-4 text-sm text-gray-700 dark:border-indigo-500/30 dark:bg-gray-900 dark:text-gray-200">
                                            <span
                                                class="text-xs font-semibold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
                                                Responde con alguna idea:
                                            </span>
                                            <button v-for="suggestion in followUpSuggestions" :key="suggestion"
                                                type="button" @click="handleStarterPrompt(suggestion)"
                                                class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-indigo-500/50 dark:bg-indigo-500/10 dark:text-indigo-200 dark:hover:bg-indigo-500/30 dark:hover:text-white">
                                                {{ suggestion }}
                                            </button>
                                        </div>

                                        <form class="space-y-3" @submit.prevent="handleSubmit">
                                            <label
                                                class="text-xs font-semibold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
                                                Escribe tu mensaje
                                            </label>
                                            <TextArea ref="messageInput" v-model="draftMessage" rows="4"
                                                class="w-full rounded-2xl border-gray-200 bg-white/90 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-100" />

                                            <div
                                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="text-xs text-rose-500" v-if="errorMessage">
                                                    {{ errorMessage }}
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" @click="handleResetChat" :disabled="isSending"
                                                        class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:hover:text-white">
                                                        <i class="fa-solid fa-rotate-right mr-2"></i>Reiniciar chat
                                                    </button>
                                                    <button type="submit" :disabled="!canSendMessage"
                                                        class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-indigo-400 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                                                        <i class="fa-solid fa-paper-plane mr-2"></i>
                                                        <span>{{ isSending ? 'Enviando...' : 'Enviar mensaje' }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
