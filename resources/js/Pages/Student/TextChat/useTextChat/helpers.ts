import type { ChatMessage, LevelOption, VocabularyItem } from './types';

export const buildId = (() => {
    let counter = 0;
    return () => `msg-${Date.now()}-${counter++}`;
})();

export const welcomeMessageForLevel = (levels: LevelOption[], levelValue: string) => {
    const selected = levels.find((level) => level.value === levelValue);
    const levelHint = selected ? selected.description : 'We will keep a balanced challenge together.';

    return [
        "Hi! I'm Nativo, your English conversation partner.",
        'Write a message to start the dialogue and I will guide you step by step.',
        levelHint,
    ].join(' ');
};

export const createInitialAssistantMessage = (
    levels: LevelOption[],
    levelValue: string,
): ChatMessage => ({
    id: buildId(),
    role: 'assistant',
    content: welcomeMessageForLevel(levels, levelValue),
    createdAt: new Date().toISOString(),
});

export const sanitizeVocabulary = (items: unknown): VocabularyItem[] => {
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

export const sanitizeStringArray = (items: unknown): string[] => {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((value) => (typeof value === 'string' ? value.trim() : ''))
        .filter((value) => value !== '');
};
