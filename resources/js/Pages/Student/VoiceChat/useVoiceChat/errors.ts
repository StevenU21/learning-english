export const mapMediaError = (error: unknown): string => {
    if (typeof error === 'string') {
        return error;
    }

    const mediaError = (typeof error === 'object' && error !== null
        ? (error as { name?: string; message?: string })
        : undefined) ?? {};

    if (mediaError.name === 'NotAllowedError' || mediaError.name === 'SecurityError') {
        return 'Necesitamos permisos del micrófono para iniciar la sesión.';
    }

    if (mediaError.name === 'NotFoundError') {
        return 'No encontramos un micrófono disponible en este dispositivo.';
    }

    if (mediaError.name === 'NotReadableError') {
        return 'Otra aplicación está usando el micrófono. Ciérrala e inténtalo de nuevo.';
    }

    if (mediaError.message === 'secure-context-required') {
        return 'Necesitamos que abras la aplicación mediante HTTPS o desde localhost para usar el micrófono.';
    }

    return mediaError.message ?? 'Ocurrió un error inesperado al capturar el audio.';
};
