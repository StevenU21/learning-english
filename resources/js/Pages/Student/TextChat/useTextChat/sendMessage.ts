import type { ComputedRef, Ref } from 'vue';
import { buildId, sanitizeStringArray, sanitizeVocabulary } from './helpers';
import type { ChatMessage, FormattedMessage } from './types';

type SendMessageDependencies = {
    messages: Ref<ChatMessage[]>;
    draftMessage: Ref<string>;
    isSending: Ref<boolean>;
    errorMessage: Ref<string>;
    formattedMessages: ComputedRef<FormattedMessage[]>;
    selectedLevel: Ref<string>;
    csrfToken: ComputedRef<string>;
};

export const createSendMessage = ({
    messages,
    draftMessage,
    isSending,
    errorMessage,
    formattedMessages,
    selectedLevel,
    csrfToken,
}: SendMessageDependencies) => {
    const createAssistantPlaceholder = (): ChatMessage => ({
        id: buildId(),
        role: 'assistant',
        content: '',
        createdAt: new Date().toISOString(),
        vocabulary: [],
        grammarTips: [],
        followUpQuestions: [],
        streaming: true,
    });

    return async function sendMessage() {
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

            assistantMessage = createAssistantPlaceholder();
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
    };
};
