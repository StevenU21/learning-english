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

            const rawAssistantMessage = createAssistantPlaceholder();
            messages.value.push(rawAssistantMessage);
            
            // In Vue 3, pushing a raw object into a ref array makes the array's version reactive.
            // We must mutate the reactive proxy, not the raw object.
            const assistantMessage = messages.value[messages.value.length - 1];

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

            const reader = response.body?.getReader();
            if (!reader) {
                throw new Error('Stream not available');
            }

            const decoder = new TextDecoder('utf-8');
            let buffer = '';

            assistantMessage.content = '';
            assistantMessage.streaming = true;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (line.trim() === '') continue;
                    if (line.startsWith('data: ')) {
                        const dataStr = line.substring(6).trim();
                        if (dataStr === '[DONE]') {
                            continue;
                        }
                        try {
                            const data = JSON.parse(dataStr);
                            const content = data.choices?.[0]?.delta?.content;
                            if (content) {
                                assistantMessage.content += content;
                                messages.value = [...messages.value];
                            }
                        } catch (e) {
                            // ignore JSON parse error on incomplete chunks
                        }
                    }
                }
            }
            
            // process any remaining buffer just in case
            if (buffer.startsWith('data: ') && buffer.trim() !== 'data: [DONE]') {
                try {
                    const data = JSON.parse(buffer.substring(6).trim());
                    const content = data.choices?.[0]?.delta?.content;
                    if (content) assistantMessage.content += content;
                } catch (e) {
                    // ignore
                }
            }

            assistantMessage.streaming = false;
            assistantMessage.vocabulary = [];
            assistantMessage.grammarTips = [];
            assistantMessage.followUpQuestions = [];
            errorMessage.value = '';
        } catch (error) {
            console.error(error);
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'We could not get a response from the tutor. Please try again later.';

            if (messages.value.length > 0 && messages.value[messages.value.length - 1].role === 'assistant') {
                const lastMsg = messages.value[messages.value.length - 1];
                lastMsg.streaming = false;
                lastMsg.content = 'I had trouble responding. Could you try sending your last message again?';
                lastMsg.vocabulary = [];
                lastMsg.grammarTips = [];
                lastMsg.followUpQuestions = [];
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

            if (messages.value.length > 0 && messages.value[messages.value.length - 1].role === 'assistant') {
                const lastMsg = messages.value[messages.value.length - 1];
                if (lastMsg.streaming) {
                    lastMsg.streaming = false;
                }
            }
        }
    };
};
