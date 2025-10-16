import { computed, ref, watch } from 'vue';

type Role = 'user' | 'assistant';

type VocabularyItem = {
    term: string;
    definition: string;
    example?: string;
};

type ChatMessage = {
    id: string;
    role: Role;
    content: string;
    createdAt: string;
    vocabulary?: VocabularyItem[];
    grammarTips?: string[];
    followUpQuestions?: string[];
};

export type LevelOption = {
    value: string;
    label: string;
    description: string;
};

export type TextChatProps = {
    levels: LevelOption[];
    defaultLevel: string;
    starterPrompts: string[];
};

const buildId = (() => {
    let counter = 0;
    return () => `msg-${Date.now()}-${counter++}`;
})();

const welcomeMessageForLevel = (levels: LevelOption[], levelValue: string) => {
    const selected = levels.find((level) => level.value === levelValue);
    const levelHint = selected ? selected.description : 'We will keep a balanced challenge together.';

    return [
        "Hi! I'm Nativo, your English conversation partner.",
        'Write a message to start the dialogue and I will guide you step by step.',
        levelHint,
    ].join(' ');
};

export const useTextChat = (props: TextChatProps) => {
    const selectedLevel = ref(
        props.levels.find((level) => level.value === props.defaultLevel)?.value ?? props.levels[0]?.value ?? 'intermedio',
    );

    const messages = ref<ChatMessage[]>([
        {
            id: buildId(),
            role: 'assistant',
            content: welcomeMessageForLevel(props.levels, selectedLevel.value),
            createdAt: new Date().toISOString(),
        },
    ]);

    const draftMessage = ref('');
    const isSending = ref(false);
    const errorMessage = ref('');

    const csrfToken = computed(() => {
        const token = document.head
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!token) {
            throw new Error('Missing CSRF token');
        }

        return token;
    });

    const hasUserInteraction = computed(() => messages.value.some((message) => message.role === 'user'));

    watch(selectedLevel, (level, previous) => {
        if (level === previous) {
            return;
        }

        if (!hasUserInteraction.value && messages.value.length === 1 && messages.value[0].role === 'assistant') {
            messages.value = [
                {
                    id: buildId(),
                    role: 'assistant',
                    content: welcomeMessageForLevel(props.levels, level),
                    createdAt: new Date().toISOString(),
                },
            ];
        }
    });

    const formattedMessages = computed(() => {
        const normalized = messages.value.map(({ role, content }) => ({
            role,
            content,
        }));

        return normalized.slice(Math.max(0, normalized.length - 12));
    });

    const followUpSuggestions = computed(() => {
        for (let index = messages.value.length - 1; index >= 0; index -= 1) {
            const message = messages.value[index];
            if (message.role === 'assistant' && message.followUpQuestions?.length) {
                return message.followUpQuestions;
            }
        }

        return [] as string[];
    });

    const latestVocabulary = computed(() => {
        for (let index = messages.value.length - 1; index >= 0; index -= 1) {
            const message = messages.value[index];
            if (message.role === 'assistant' && message.vocabulary?.length) {
                return message.vocabulary;
            }
        }

        return [] as VocabularyItem[];
    });

    const latestGrammarTips = computed(() => {
        for (let index = messages.value.length - 1; index >= 0; index -= 1) {
            const message = messages.value[index];
            if (message.role === 'assistant' && message.grammarTips?.length) {
                return message.grammarTips;
            }
        }

        return [] as string[];
    });

    async function sendMessage() {
        const trimmed = draftMessage.value.trim();

        if (!trimmed || isSending.value) {
            return;
        }

        errorMessage.value = '';

        const userMessage: ChatMessage = {
            id: buildId(),
            role: 'user',
            content: trimmed,
            createdAt: new Date().toISOString(),
        };

        messages.value.push(userMessage);
        draftMessage.value = '';
        isSending.value = true;

        try {
            const response = await fetch(route('student.text-chat.message'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    messages: formattedMessages.value,
                    level: selectedLevel.value,
                }),
            });

            if (!response.ok) {
                const result = await response.json().catch(() => ({} as { message?: string }));
                throw new Error(result.message ?? 'Unable to send the message right now.');
            }

            const payload = (await response.json()) as {
                reply: string;
                vocabulary?: VocabularyItem[];
                grammarTips?: string[];
                followUpQuestions?: string[];
            };

            messages.value.push({
                id: buildId(),
                role: 'assistant',
                content: payload.reply,
                vocabulary: payload.vocabulary ?? [],
                grammarTips: payload.grammarTips ?? [],
                followUpQuestions: payload.followUpQuestions ?? [],
                createdAt: new Date().toISOString(),
            });
        } catch (error) {
            console.error(error);
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'We could not get a response from the tutor. Please try again later.';

            messages.value.push({
                id: buildId(),
                role: 'assistant',
                content: 'I had trouble responding. Could you try sending your last message again?',
                createdAt: new Date().toISOString(),
            });
        } finally {
            isSending.value = false;
        }
    }

    function resetChat() {
        messages.value = [
            {
                id: buildId(),
                role: 'assistant',
                content: welcomeMessageForLevel(props.levels, selectedLevel.value),
                createdAt: new Date().toISOString(),
            },
        ];
        draftMessage.value = '';
        errorMessage.value = '';
    }

    function applySuggestion(suggestion: string) {
        draftMessage.value = suggestion;
    }

    return {
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
    };
};
