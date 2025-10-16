import type { ChatMessage, VocabularyItem } from './types';

const isCompletedAssistantMessage = (message: ChatMessage) =>
    message.role === 'assistant' && !message.streaming;

const findLatestAssistantMatch = <T>(
    messages: ChatMessage[],
    resolve: (message: ChatMessage) => T | null,
): T | null => {
    for (let index = messages.length - 1; index >= 0; index -= 1) {
        const candidate = messages[index];

        if (!isCompletedAssistantMessage(candidate)) {
            continue;
        }

        const result = resolve(candidate);

        if (result) {
            return result;
        }
    }

    return null;
};

export const selectFollowUpSuggestions = (messages: ChatMessage[]): string[] =>
    findLatestAssistantMatch(messages, (message) => {
        if (message.followUpQuestions && message.followUpQuestions.length > 0) {
            return message.followUpQuestions;
        }

        return null;
    }) ?? [];

export const selectLatestVocabulary = (messages: ChatMessage[]): VocabularyItem[] =>
    findLatestAssistantMatch(messages, (message) => {
        if (message.vocabulary && message.vocabulary.length > 0) {
            return message.vocabulary;
        }

        return null;
    }) ?? [];

export const selectLatestGrammarTips = (messages: ChatMessage[]): string[] =>
    findLatestAssistantMatch(messages, (message) => {
        if (message.grammarTips && message.grammarTips.length > 0) {
            return message.grammarTips;
        }

        return null;
    }) ?? [];
