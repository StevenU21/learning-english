import type { Ref } from 'vue';
import type { VoiceChatSessionData } from './types';

type CountdownManagerOptions = {
    countdown: Ref<number>;
    getDefaultDuration: () => number;
};

type StartCountdownOptions = {
    duration: number;
    onExpire: () => void;
};

export const createCountdownManager = ({ countdown, getDefaultDuration }: CountdownManagerOptions) => {
    let countdownInterval: ReturnType<typeof window.setInterval> | null = null;
    let sessionTimeout: ReturnType<typeof window.setTimeout> | null = null;

    const clearTimers = () => {
        if (countdownInterval !== null) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        if (sessionTimeout !== null) {
            clearTimeout(sessionTimeout);
            sessionTimeout = null;
        }
    };

    const reset = () => {
        clearTimers();
        countdown.value = getDefaultDuration();
    };

    const start = ({ duration, onExpire }: StartCountdownOptions) => {
        clearTimers();

        const seconds = Math.max(1, Math.floor(duration));
        countdown.value = seconds;

        let expired = false;

        const handleExpire = () => {
            if (expired) {
                return;
            }

            expired = true;
            clearTimers();
            countdown.value = getDefaultDuration();
            onExpire();
        };

        countdownInterval = window.setInterval(() => {
            countdown.value -= 1;

            if (countdown.value <= 0) {
                handleExpire();
            }
        }, 1000);

        sessionTimeout = window.setTimeout(handleExpire, seconds * 1000);
    };

    return {
        start,
        reset,
    } as const;
};

export const resolveSessionDuration = (
    defaultDuration: number,
    data?: VoiceChatSessionData | null,
) => {
    if (!data) {
        return defaultDuration;
    }

    const expiresIn = Number(data.expires_in);
    if (Number.isFinite(expiresIn) && expiresIn > 0) {
        return expiresIn;
    }

    const expiresAtRaw = data.expires_at;
    if (expiresAtRaw !== null && expiresAtRaw !== undefined) {
        const expiresAtMs =
            typeof expiresAtRaw === 'number'
                ? expiresAtRaw > 1e12
                    ? expiresAtRaw
                    : expiresAtRaw * 1000
                : Date.parse(String(expiresAtRaw));

        if (Number.isFinite(expiresAtMs)) {
            const remaining = Math.max(0, Math.round((expiresAtMs - Date.now()) / 1000));
            if (remaining > 0) {
                return remaining;
            }
        }
    }

    return defaultDuration;
};
