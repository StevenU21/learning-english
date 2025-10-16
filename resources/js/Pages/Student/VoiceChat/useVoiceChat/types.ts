export type VoiceChatProps = {
    defaultVoice: string;
    defaultModel: string;
    sessionDuration: number;
};

export type VoiceChatSessionData = {
    model: string;
    client_secret: string;
    expires_in?: number | string | null;
    expires_at?: number | string | null;
};

export type StartSessionOptions = {
    voice?: string;
    level?: string;
    instructions?: string;
};

export type AudioKind = 'local' | 'remote';
