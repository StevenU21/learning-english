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
    streaming?: boolean;
    raw?: Record<string, unknown>;
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

const sanitizeVocabulary = (items: unknown): VocabularyItem[] => {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((entry) => {
            const term = typeof entry?.term === 'string' ? entry.term.trim() : '';
            const definition = typeof entry?.definition === 'string' ? entry.definition.trim() : '';
            const example = typeof entry?.example === 'string' ? entry.example.trim() : '';

            if (!term || !definition) {
                return null;
            }

            return example
                ? ({ term, definition, example } satisfies VocabularyItem)
                : ({ term, definition } satisfies VocabularyItem);
        })
        .filter((entry): entry is VocabularyItem => entry !== null);
};

const sanitizeStringArray = (items: unknown): string[] => {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((value) => (typeof value === 'string' ? value.trim() : ''))
        .filter((value) => value !== '');
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
            if (message.role === 'assistant' && !message.streaming && message.followUpQuestions?.length) {
                return message.followUpQuestions;
            }
        }

        return [] as string[];
    });

    const latestVocabulary = computed(() => {
        for (let index = messages.value.length - 1; index >= 0; index -= 1) {
            const message = messages.value[index];
            if (message.role === 'assistant' && !message.streaming && message.vocabulary?.length) {
                return message.vocabulary;
            }
        }

        return [] as VocabularyItem[];
    });

    const latestGrammarTips = computed(() => {
        for (let index = messages.value.length - 1; index >= 0; index -= 1) {
            const message = messages.value[index];
            if (message.role === 'assistant' && !message.streaming && message.grammarTips?.length) {
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

        let assistantMessage: ChatMessage | null = null;

        try {
            const payloadMessages = formattedMessages.value;

            assistantMessage = {
                id: buildId(),
                role: 'assistant',
                content: '',
                createdAt: new Date().toISOString(),
                vocabulary: [],
                grammarTips: [],
                followUpQuestions: [],
                streaming: true,
            };

            messages.value.push(assistantMessage);

            const response = await fetch(route('student.text-chat.message'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    messages: payloadMessages,
                    level: selectedLevel.value,
                }),
            });

            if (!response.ok) {
                let message = 'Unable to send the message right now.';

                try {
                    const result = await response.json();
                    if (result?.message) {
                        message = String(result.message);
                    }
                } catch (parseError) {
                    console.error(parseError);
                }

                throw new Error(message);
            }

            const result = (await response.json()) as Record<string, unknown>;

            const replyText = typeof result.reply === 'string' ? result.reply.trim() : '';
            const vocabulary = sanitizeVocabulary(result.vocabulary);
            const grammarFromSnake = sanitizeStringArray(result.grammar_tips);
            const grammarFromCamel = sanitizeStringArray(result.grammarTips);
            const followFromSnake = sanitizeStringArray(result.follow_up_questions);
            const followFromCamel = sanitizeStringArray(result.followUpQuestions);

            assistantMessage.content =
                replyText || 'I am ready to continue! Could you restate your last question so I can respond?';
            assistantMessage.vocabulary = vocabulary;
            assistantMessage.grammarTips = grammarFromSnake.length ? grammarFromSnake : grammarFromCamel;
            assistantMessage.followUpQuestions = followFromSnake.length ? followFromSnake : followFromCamel;
            assistantMessage.streaming = false;

            if (typeof result.raw === 'object' && result.raw !== null) {
                assistantMessage.raw = result.raw as Record<string, unknown>;
            }

            errorMessage.value = '';
        } catch (error) {
            console.error(error);
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'We could not get a response from the tutor. Please try again later.';

            if (assistantMessage) {
                assistantMessage.streaming = false;
                assistantMessage.content = 'I had trouble responding. Could you try sending your last message again?';
                assistantMessage.vocabulary = [];
                assistantMessage.grammarTips = [];
                assistantMessage.followUpQuestions = [];
            } else {
                messages.value.push({
                    id: buildId(),
                    role: 'assistant',
                    content: 'I had trouble responding. Could you try sending your last message again?',
                    createdAt: new Date().toISOString(),
                });
            }
        } finally {
            isSending.value = false;

            if (assistantMessage && assistantMessage.streaming) {
                assistantMessage.streaming = false;
            }
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
