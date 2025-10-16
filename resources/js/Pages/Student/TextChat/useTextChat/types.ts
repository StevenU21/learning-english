export type Role = 'user' | 'assistant';

export type VocabularyItem = {
    term: string;
    definition: string;
    example?: string;
};

export type ChatMessage = {
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

export type FormattedMessage = {
    role: Role;
    content: string;
};
