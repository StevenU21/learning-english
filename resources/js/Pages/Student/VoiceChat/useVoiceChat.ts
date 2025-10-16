import { computed, onBeforeUnmount, ref, type Ref } from 'vue';

export type VoiceChatProps = {
    defaultVoice: string;
    defaultModel: string;
    sessionDuration: number;
};

type VoiceChatSessionData = {
    model: string;
    client_secret: string;
    expires_in?: number | string | null;
    expires_at?: number | string | null;
};

type StartSessionOptions = {
    voice?: string;
    level?: string;
    instructions?: string;
};

type LegacyGetUserMedia = (
    constraints: MediaStreamConstraints,
    successCallback: (stream: MediaStream) => void,
    errorCallback: (error: unknown) => void,
) => void;

type LegacyNavigator = Navigator & {
    getUserMedia?: LegacyGetUserMedia;
    webkitGetUserMedia?: LegacyGetUserMedia;
    mozGetUserMedia?: LegacyGetUserMedia;
    msGetUserMedia?: LegacyGetUserMedia;
};

type AudioKind = 'local' | 'remote';

export const useVoiceChat = (props: VoiceChatProps) => {
    const statusMessage = ref('Listo para iniciar tu práctica de conversación.');
    const isConnecting = ref(false);
    const isActive = ref(false);
    const countdown = ref(props.sessionDuration);
    const errorMessage = ref('');
    const remoteAudioEl: Ref<HTMLAudioElement | null> = ref(null);
    const localLevel = ref(0);
    const remoteLevel = ref(0);
    const localSpectrum = ref<number[]>(Array(12).fill(0));
    const remoteSpectrum = ref<number[]>(Array(12).fill(0));

    let peerConnection: RTCPeerConnection | null = null;
    let localStream: MediaStream | null = null;
    let remoteStream: MediaStream | null = null;
    let countdownInterval: ReturnType<typeof window.setInterval> | null = null;
    let sessionTimeout: ReturnType<typeof window.setTimeout> | null = null;
    let visualizerFrame: number | null = null;

    const audioContexts: Partial<Record<AudioKind, AudioContext>> = {};
    const audioAnalysers: Partial<Record<AudioKind, AnalyserNode>> = {};
    const audioSources: Partial<Record<AudioKind, MediaStreamAudioSourceNode>> = {};

    const csrfToken = computed(() => {
        const token = document.head
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!token) {
            throw new Error('No se encontró el token CSRF en el documento.');
        }

        return token;
    });

    function resetCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        if (sessionTimeout) {
            clearTimeout(sessionTimeout);
            sessionTimeout = null;
        }

        countdown.value = props.sessionDuration;
    }

    function buildSpectrum(data: Uint8Array) {
        const bucketCount = 12;
        const spectrum: number[] = new Array(bucketCount).fill(0);
        const bucketSize = Math.max(1, Math.floor(data.length / bucketCount));

        for (let bucket = 0; bucket < bucketCount; bucket += 1) {
            const start = bucket * bucketSize;
            let sum = 0;
            let count = 0;

            for (let index = start; index < start + bucketSize && index < data.length; index += 1) {
                sum += data[index];
                count += 1;
            }

            const average = count > 0 ? sum / count : 0;
            spectrum[bucket] = Math.min(100, Math.round((average / 255) * 100));
        }

        return spectrum;
    }

    function updateLevels(kind: AudioKind) {
        const analyser = audioAnalysers[kind];

        if (!analyser) {
            if (kind === 'local') {
                localLevel.value = 0;
                localSpectrum.value = Array(12).fill(0);
            } else {
                remoteLevel.value = 0;
                remoteSpectrum.value = Array(12).fill(0);
            }

            return;
        }

        const frequencyData = new Uint8Array(analyser.frequencyBinCount);
        analyser.getByteFrequencyData(frequencyData);

        const total = frequencyData.reduce((accumulator, value) => accumulator + value, 0);
        const average = total / frequencyData.length;
        const level = Math.min(100, Math.round((average / 255) * 100));

        if (kind === 'local') {
            localLevel.value = level;
            localSpectrum.value = buildSpectrum(frequencyData);
        } else {
            remoteLevel.value = level;
            remoteSpectrum.value = buildSpectrum(frequencyData);
        }
    }

    function ensureVisualizerRunning() {
        if (visualizerFrame !== null) {
            return;
        }

        const loop = () => {
            updateLevels('local');
            updateLevels('remote');
            visualizerFrame = window.requestAnimationFrame(loop);
        };

        visualizerFrame = window.requestAnimationFrame(loop);
    }

    function stopVisualizer() {
        if (visualizerFrame !== null) {
            window.cancelAnimationFrame(visualizerFrame);
            visualizerFrame = null;
        }
    }

    function detachAnalyzer(kind: AudioKind) {
        audioSources[kind]?.disconnect();
        audioAnalysers[kind]?.disconnect();
        audioContexts[kind]?.close().catch(() => undefined);

        delete audioSources[kind];
        delete audioAnalysers[kind];
        delete audioContexts[kind];
    }

    function attachAnalyzer(kind: AudioKind, stream: MediaStream | null) {
        if (!stream) {
            return;
        }

        detachAnalyzer(kind);

        try {
            const context = new AudioContext();
            const source = context.createMediaStreamSource(stream);
            const analyser = context.createAnalyser();
            analyser.fftSize = 256;

            source.connect(analyser);

            audioContexts[kind] = context;
            audioSources[kind] = source;
            audioAnalysers[kind] = analyser;

            ensureVisualizerRunning();
        } catch (error) {
            console.error('No se pudo inicializar el analizador de audio', error);
        }
    }

    function cleanupAnalyzers() {
        detachAnalyzer('local');
        detachAnalyzer('remote');
        stopVisualizer();
        localLevel.value = 0;
        remoteLevel.value = 0;
        localSpectrum.value = Array(12).fill(0);
        remoteSpectrum.value = Array(12).fill(0);
    }

    function cleanupMedia() {
        if (peerConnection) {
            try {
                peerConnection.getSenders().forEach((sender) => {
                    peerConnection?.removeTrack(sender);
                });
            } catch (_) {
                // Ignored: removing tracks can throw if the connection is already closed.
            }

            peerConnection.ontrack = null;
            peerConnection.onconnectionstatechange = null;
            peerConnection.close();
        }

        if (localStream) {
            localStream.getTracks().forEach((track) => track.stop());
        }

        if (remoteStream) {
            remoteStream.getTracks().forEach((track) => track.stop());
        }

        if (remoteAudioEl.value) {
            remoteAudioEl.value.srcObject = null;
        }

        peerConnection = null;
        localStream = null;
        remoteStream = null;

        cleanupAnalyzers();
        resetCountdown();
        isActive.value = false;
    }

    function stopSession(message = 'Sesión finalizada.') {
        if (!isActive.value && !peerConnection) {
            statusMessage.value = message;
            return;
        }

        cleanupMedia();
        statusMessage.value = message;
    }

    function startCountdown(duration: number) {
        resetCountdown();
        const seconds = Math.max(1, Math.floor(duration));
        countdown.value = seconds;

        countdownInterval = window.setInterval(() => {
            countdown.value -= 1;
            if (countdown.value <= 0) {
                stopSession('Tiempo agotado. ¡Buen trabajo!');
            }
        }, 1000);

        sessionTimeout = window.setTimeout(() => {
            stopSession('Tiempo agotado. ¡Buen trabajo!');
        }, seconds * 1000);
    }

    function getSessionDuration(data?: VoiceChatSessionData | null) {
        if (!data) {
            return props.sessionDuration;
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

        return props.sessionDuration;
    }

    function mapMediaError(error: unknown): string {
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
    }

    async function getMicrophoneStream(): Promise<MediaStream> {
        const constraints: MediaStreamConstraints = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                channelCount: 1,
                sampleRate: 16000,
            },
        };

        if (navigator?.mediaDevices?.getUserMedia) {
            return navigator.mediaDevices.getUserMedia(constraints);
        }

        const legacyNavigator = navigator as LegacyNavigator;
        const legacyGetUserMedia =
            legacyNavigator.getUserMedia ??
            legacyNavigator.webkitGetUserMedia ??
            legacyNavigator.mozGetUserMedia ??
            legacyNavigator.msGetUserMedia;

        if (legacyGetUserMedia) {
            return new Promise((resolve, reject) => {
                legacyGetUserMedia.call(legacyNavigator, constraints, resolve, reject);
            });
        }

        throw new Error('Tu navegador no soporta captura de audio.');
    }

    async function startSession(options?: StartSessionOptions) {
        if (isConnecting.value || isActive.value) {
            return;
        }

        errorMessage.value = '';
        statusMessage.value = 'Preparando tu sesión de voz...';
        isConnecting.value = true;

        try {
            localStream = await getMicrophoneStream();
            attachAnalyzer('local', localStream);

            statusMessage.value = 'Conectando con la IA...';

            const sessionPayload: Record<string, unknown> = {
                voice: options?.voice ?? props.defaultVoice,
            };

            if (options?.level) {
                sessionPayload.level = options.level;
            }

            if (options?.instructions) {
                sessionPayload.instructions = options.instructions;
            }

            const sessionResponse = await fetch(route('student.voice-chat.session'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                    Accept: 'application/json',
                },
                body: JSON.stringify(sessionPayload),
            });

            if (!sessionResponse.ok) {
                const problem = await sessionResponse.json().catch(() => ({} as { message?: string }));
                throw new Error(problem.message ?? 'No se pudo crear la sesión en el servidor.');
            }

            const sessionData = (await sessionResponse.json()) as VoiceChatSessionData;

            const connection = new RTCPeerConnection({
                iceServers: [],
            });

            peerConnection = connection;
            remoteStream = new MediaStream();
            if (remoteAudioEl.value) {
                remoteAudioEl.value.srcObject = remoteStream;
            }

            connection.ontrack = (event) => {
                if (event.streams && event.streams[0]) {
                    remoteStream = event.streams[0];
                } else {
                    if (!remoteStream) {
                        remoteStream = new MediaStream();
                    }

                    if (event.track) {
                        remoteStream.addTrack(event.track);
                    }
                }

                if (remoteAudioEl.value) {
                    remoteAudioEl.value.srcObject = remoteStream;
                }

                attachAnalyzer('remote', remoteStream);
            };

            connection.onconnectionstatechange = () => {
                if (['failed', 'disconnected', 'closed'].includes(connection.connectionState)) {
                    stopSession('La conexión se cerró.');
                }
            };

            const activeLocalStream = localStream;
            if (!activeLocalStream) {
                throw new Error('No se pudo acceder al micrófono.');
            }

            activeLocalStream.getTracks().forEach((track) => {
                connection.addTrack(track, activeLocalStream);
            });

            const offer = await connection.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: false,
            });

            await connection.setLocalDescription(offer);

            const sdpResponse = await fetch(
                `https://api.openai.com/v1/realtime?model=${encodeURIComponent(sessionData.model)}`,
                {
                    method: 'POST',
                    headers: {
                        Authorization: `Bearer ${sessionData.client_secret}`,
                        'Content-Type': 'application/sdp',
                        'OpenAI-Beta': 'realtime=v1',
                    },
                    body: offer.sdp ?? '',
                },
            );

            if (!sdpResponse.ok) {
                throw new Error('OpenAI no aceptó la negociación de audio.');
            }

            const answerSdp = await sdpResponse.text();
            await connection.setRemoteDescription({ type: 'answer', sdp: answerSdp });

            if (remoteAudioEl.value && remoteAudioEl.value.paused) {
                remoteAudioEl.value.play().catch(() => {
                    // Algunos navegadores móviles requieren interacción adicional para reproducir audio.
                });
            }

            startCountdown(getSessionDuration(sessionData));

            statusMessage.value = 'Sesión activa. ¡Hablemos!';
            isActive.value = true;
        } catch (error) {
            console.error(error);
            cleanupMedia();
            errorMessage.value = mapMediaError(error);
            statusMessage.value = 'No se pudo iniciar la sesión.';
        } finally {
            isConnecting.value = false;
        }
    }

    onBeforeUnmount(() => {
        cleanupMedia();
    });

    const isUserSpeaking = computed(() => isActive.value && localLevel.value > 25);
    const isAiSpeaking = computed(() => isActive.value && remoteLevel.value > 25);

    return {
        statusMessage,
        isConnecting,
        isActive,
        countdown,
        errorMessage,
        remoteAudioEl,
        startSession,
        stopSession,
        localLevel,
        remoteLevel,
        localSpectrum,
        remoteSpectrum,
        isUserSpeaking,
        isAiSpeaking,
    } as const;
};
