import { computed, ref, watch } from 'vue';
import { createSendMessage } from './sendMessage';
import { createInitialAssistantMessage } from './helpers';
import {
    selectFollowUpSuggestions,
    selectLatestGrammarTips,
    selectLatestVocabulary,
} from './selectors';
import type { ChatMessage, FormattedMessage, TextChatProps } from './types';

const buildFormattedMessages = (messages: ChatMessage[]): FormattedMessage[] => {
    const normalized = messages.map(({ role, content }) => ({
        role,
        content,
    }));

    return normalized.slice(Math.max(0, normalized.length - 12));
};

export const useTextChat = (props: TextChatProps) => {
    const initialLevel = props.levels.find((level) => level.value === props.defaultLevel)?.value;

    const selectedLevel = ref(initialLevel ?? props.levels[0]?.value ?? 'intermedio');
    const messages = ref<ChatMessage[]>([
        createInitialAssistantMessage(props.levels, selectedLevel.value),
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

    const hasUserInteraction = computed(() =>
        messages.value.some((message) => message.role === 'user'),
    );

    watch(selectedLevel, (level, previous) => {
        if (level === previous) {
            return;
        }

        if (!hasUserInteraction.value && messages.value.length === 1 && messages.value[0].role === 'assistant') {
            messages.value = [createInitialAssistantMessage(props.levels, level)];
        }
    });

    const formattedMessages = computed(() => buildFormattedMessages(messages.value));

    const followUpSuggestions = computed(() => selectFollowUpSuggestions(messages.value));
    const latestVocabulary = computed(() => selectLatestVocabulary(messages.value));
    const latestGrammarTips = computed(() => selectLatestGrammarTips(messages.value));

    const sendMessage = createSendMessage({
        messages,
        draftMessage,
        isSending,
        errorMessage,
        formattedMessages,
        selectedLevel,
        csrfToken,
    });

    function resetChat() {
        messages.value = [createInitialAssistantMessage(props.levels, selectedLevel.value)];
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
    } as const;
};

export type { TextChatProps } from './types';
