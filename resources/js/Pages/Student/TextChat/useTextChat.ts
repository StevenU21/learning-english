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

            const response = await fetch(route('student.text-chat.message'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                    Accept: 'text/event-stream',
                },
                body: JSON.stringify({
                    messages: payloadMessages,
                    level: selectedLevel.value,
                }),
            });

            if (!response.ok || !response.body) {
                let message = 'Unable to send the message right now.';

                try {
                    const result = await response.json();
                    if (result?.message) {
                        message = String(result.message);
                    }
                } catch (error) {
                    console.error(error);
                }

                throw new Error(message);
            }

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
            const streamIssue = await consumeStream(response.body, assistantMessage);

            if (streamIssue) {
                errorMessage.value = streamIssue;
            } else {
                errorMessage.value = '';
            }
        } catch (error) {
            console.error(error);
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'We could not get a response from the tutor. Please try again later.';

            if (assistantMessage) {
                assistantMessage.streaming = false;
                assistantMessage.content = 'I had trouble responding. Could you try sending your last message again?';
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

    async function consumeStream(stream: ReadableStream<Uint8Array>, target: ChatMessage): Promise<string | null> {
        const reader = stream.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let completed = false;
        let streamError: string | null = null;
        const replyToken = '"reply"';
        let replyTokenProgress = 0;
        let streamStage: 'seekKey' | 'seekColon' | 'seekQuote' | 'streamValue' | 'done' = 'seekKey';
        let escapeMode: 'none' | 'simple' | 'unicode' = 'none';
        let unicodeBuffer = '';
        let replyValue = '';

        const appendReplyText = (text: string) => {
            if (!text) {
                return;
            }

            replyValue += text;
            target.content = replyValue;
        };

        const decodeSimpleEscape = (sequence: string): string => {
            switch (sequence) {
                case '"':
                    return '"';
                case '\\':
                    return '\\';
                case '/':
                    return '/';
                case 'b':
                    return '\b';
                case 'f':
                    return '\f';
                case 'n':
                    return '\n';
                case 'r':
                    return '\r';
                case 't':
                    return '\t';
                default:
                    return sequence;
            }
        };

        const processUnicodeEscape = (char: string) => {
            if (!/[0-9a-fA-F]/.test(char)) {
                appendReplyText(`\\u${unicodeBuffer}${char}`);
                unicodeBuffer = '';
                escapeMode = 'none';
                return;
            }

            unicodeBuffer += char;

            if (unicodeBuffer.length < 4) {
                return;
            }

            const codePoint = Number.parseInt(unicodeBuffer, 16);

            if (Number.isNaN(codePoint)) {
                appendReplyText(`\\u${unicodeBuffer}`);
            } else {
                appendReplyText(String.fromCharCode(codePoint));
            }

            unicodeBuffer = '';
            escapeMode = 'none';
        };

        const processDeltaText = (chunk: string) => {
            for (let index = 0; index < chunk.length; index += 1) {
                const char = chunk[index];

                if (streamStage === 'done') {
                    return;
                }

                if (streamStage === 'seekKey') {
                    if (char === replyToken[replyTokenProgress]) {
                        replyTokenProgress += 1;

                        if (replyTokenProgress === replyToken.length) {
                            streamStage = 'seekColon';
                            replyTokenProgress = 0;
                        }

                        continue;
                    }

                    replyTokenProgress = char === replyToken[0] ? 1 : 0;
                    continue;
                }

                if (streamStage === 'seekColon') {
                    if (char === ':') {
                        streamStage = 'seekQuote';
                        continue;
                    }

                    if (char.trim() === '') {
                        continue;
                    }

                    streamStage = 'seekKey';
                    replyTokenProgress = 0;
                    index -= 1;
                    continue;
                }

                if (streamStage === 'seekQuote') {
                    if (char === '"') {
                        streamStage = 'streamValue';
                        escapeMode = 'none';
                        unicodeBuffer = '';
                        continue;
                    }

                    if (char.trim() === '') {
                        continue;
                    }

                    streamStage = 'seekKey';
                    replyTokenProgress = 0;
                    index -= 1;
                    continue;
                }

                if (streamStage === 'streamValue') {
                    if (escapeMode === 'simple') {
                        if (char === 'u') {
                            escapeMode = 'unicode';
                            unicodeBuffer = '';
                            continue;
                        }

                        appendReplyText(decodeSimpleEscape(char));
                        escapeMode = 'none';
                        continue;
                    }

                    if (escapeMode === 'unicode') {
                        processUnicodeEscape(char);
                        continue;
                    }

                    if (char === '\\') {
                        escapeMode = 'simple';
                        continue;
                    }

                    if (char === '"') {
                        streamStage = 'done';
                        escapeMode = 'none';
                        unicodeBuffer = '';
                        continue;
                    }

                    appendReplyText(char);
                }
            }
        };

        const applyCompletion = (payload: Record<string, unknown>) => {
            const replyText = typeof payload.reply === 'string' && payload.reply.trim() !== ''
                ? payload.reply
                : null;
            const vocabulary = Array.isArray(payload.vocabulary) ? (payload.vocabulary as VocabularyItem[]) : [];
            const grammarFromSnake = Array.isArray(payload.grammar_tips) ? (payload.grammar_tips as string[]) : [];
            const grammarFromCamel = Array.isArray(payload.grammarTips) ? (payload.grammarTips as string[]) : [];
            const followFromSnake = Array.isArray(payload.follow_up_questions)
                ? (payload.follow_up_questions as string[])
                : [];
            const followFromCamel = Array.isArray(payload.followUpQuestions)
                ? (payload.followUpQuestions as string[])
                : [];

            if (replyText) {
                target.content = replyText;
            }
            target.vocabulary = vocabulary;
            target.grammarTips = grammarFromSnake.length ? grammarFromSnake : grammarFromCamel;
            target.followUpQuestions = followFromSnake.length ? followFromSnake : followFromCamel;
        };

        const processEvent = (rawEvent: string) => {
            const lines = rawEvent.split('\n');
            const dataLines: string[] = [];
            let sseEvent = 'message';

            for (const rawLine of lines) {
                const line = rawLine.trim();

                if (line === '' || line.startsWith(':')) {
                    continue;
                }

                if (line.startsWith('event:')) {
                    const declared = line.slice(6).trim();
                    if (declared !== '') {
                        sseEvent = declared;
                    }
                    continue;
                }

                if (line.startsWith('data:')) {
                    dataLines.push(line.slice(5).trim());
                }
            }

            if (dataLines.length === 0) {
                return;
            }

            const payloadText = dataLines.join('\n').trim();

            if (payloadText === '' || payloadText === '[DONE]') {
                return;
            }

            let parsed: Record<string, unknown>;

            try {
                parsed = JSON.parse(payloadText) as Record<string, unknown>;
            } catch (parseError) {
                console.error('Failed to parse streaming chunk', parseError, payloadText);
                return;
            }

            const type = typeof parsed.type === 'string' ? parsed.type : sseEvent;

            if (type === 'delta' && typeof parsed.text === 'string') {
                processDeltaText(parsed.text);
                return;
            }

            if (type === 'complete' && typeof parsed.message === 'object' && parsed.message !== null) {
                applyCompletion(parsed.message as Record<string, unknown>);
                completed = true;
                return;
            }

            if (type === 'error') {
                const message = typeof parsed.message === 'string'
                    ? parsed.message
                    : 'We could not get a response from the tutor. Please try again later.';

                streamError = message;
            }
        };

        try {
            while (true) {
                const { value, done } = await reader.read();

                if (done) {
                    buffer += decoder.decode();
                } else {
                    buffer += decoder.decode(value, { stream: true });
                }

                let delimiter = buffer.indexOf('\n\n');

                while (delimiter !== -1) {
                    const rawEvent = buffer.slice(0, delimiter);
                    buffer = buffer.slice(delimiter + 2);
                    processEvent(rawEvent);

                    if (completed || streamError) {
                        break;
                    }

                    delimiter = buffer.indexOf('\n\n');
                }

                if (completed || streamError) {
                    break;
                }

                if (done) {
                    break;
                }
            }

            if (!completed && !streamError && buffer.trim() !== '') {
                processEvent(buffer);
                buffer = '';
            }
        } finally {
            target.streaming = false;

            try {
                if (stream.locked) {
                    reader.releaseLock();
                }
            } catch (releaseError) {
                console.error('Failed to release stream reader', releaseError);
            }
        }

        if (streamError) {
            const errorText = typeof streamError === 'string' ? streamError : '';
            const trimmedError = errorText.trim();
            const fallback = trimmedError === ''
                ? 'We could not get a response from the tutor. Please try again later.'
                : trimmedError;
            target.content = fallback;
            target.vocabulary = [];
            target.grammarTips = [];
            target.followUpQuestions = [];

            return fallback;
        }

        if (!completed) {
            if (target.content.trim() === '') {
                target.content = replyValue.trim() !== ''
                    ? replyValue
                    : 'I could not finish my response. Could you try sending your last message again?';
            }

            target.vocabulary = [];
            target.grammarTips = [];
            target.followUpQuestions = [];

            return 'The tutor could not finish the message. Please try again.';
        }

        if (target.content.trim() === '') {
            target.content = 'I am ready to continue! Could you restate your last question so I can respond?';
        }

        return null;
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
