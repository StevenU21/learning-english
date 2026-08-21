import { computed, onBeforeUnmount, ref, type Ref } from 'vue';
import { createAudioAnalyserManager } from './audioAnalyserManager';
import { createCountdownManager, resolveSessionDuration } from './countdownManager';
import { mapMediaError } from './errors';
import { getMicrophoneStream } from './microphone';
import type { StartSessionOptions, VoiceChatProps, VoiceChatSessionData } from './types';

const createRemoteStream = () => new MediaStream();

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

    const audioManager = createAudioAnalyserManager({
        localLevel,
        remoteLevel,
        localSpectrum,
        remoteSpectrum,
    });

    const countdownManager = createCountdownManager({
        countdown,
        getDefaultDuration: () => props.sessionDuration,
    });

    const csrfToken = computed(() => {
        const token = document.head
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!token) {
            throw new Error('No se encontró el token CSRF en el documento.');
        }

        return token;
    });

    const detachConnectionTracks = () => {
        if (!peerConnection) {
            return;
        }

        try {
            peerConnection
                .getSenders()
                .forEach((sender) => {
                    peerConnection?.removeTrack(sender);
                });
        } catch (_) {
            // Ignored: removing tracks can throw if the connection is already closed.
        }
    };

    const closeStreams = () => {
        if (localStream) {
            localStream.getTracks().forEach((track) => track.stop());
        }

        if (remoteStream) {
            remoteStream.getTracks().forEach((track) => track.stop());
        }

        if (remoteAudioEl.value) {
            remoteAudioEl.value.srcObject = null;
        }

        localStream = null;
        remoteStream = null;
    };

    const cleanupMedia = () => {
        if (peerConnection) {
            detachConnectionTracks();
            peerConnection.ontrack = null;
            peerConnection.onconnectionstatechange = null;
            peerConnection.close();
        }

        peerConnection = null;

        closeStreams();
        audioManager.cleanup();
        countdownManager.reset();
        isActive.value = false;
    };

    const stopSession = (message = 'Sesión finalizada.') => {
        if (!isActive.value && !peerConnection) {
            statusMessage.value = message;
            return;
        }

        cleanupMedia();
        statusMessage.value = message;
    };

    const handleRemoteTrack = (event: RTCTrackEvent) => {
        if (event.streams && event.streams[0]) {
            remoteStream = event.streams[0];
        } else {
            if (!remoteStream) {
                remoteStream = createRemoteStream();
            }

            if (event.track) {
                remoteStream.addTrack(event.track);
            }
        }

        if (remoteAudioEl.value) {
            remoteAudioEl.value.srcObject = remoteStream;
        }

        audioManager.attach('remote', remoteStream);
    };

    const addLocalTracks = (connection: RTCPeerConnection, stream: MediaStream) => {
        stream.getTracks().forEach((track) => {
            connection.addTrack(track, stream);
        });
    };

    const handleConnectionStateChange = (connection: RTCPeerConnection) => {
        if (['failed', 'disconnected', 'closed'].includes(connection.connectionState)) {
            stopSession('La conexión se cerró.');
        }
    };

    const playRemoteAudio = () => {
        if (!remoteAudioEl.value || !remoteAudioEl.value.paused) {
            return;
        }

        remoteAudioEl.value.play().catch(() => {
            // Algunos navegadores móviles requieren interacción adicional para reproducir audio.
        });
    };

    const startSession = async (options?: StartSessionOptions) => {
        if (isConnecting.value || isActive.value) {
            return;
        }

        errorMessage.value = '';
        statusMessage.value = 'Preparando tu sesión de voz...';
        isConnecting.value = true;

        try {
            localStream = await getMicrophoneStream();
            audioManager.attach('local', localStream);

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
            remoteStream = createRemoteStream();

            if (remoteAudioEl.value) {
                remoteAudioEl.value.srcObject = remoteStream;
            }

            connection.ontrack = handleRemoteTrack;
            connection.onconnectionstatechange = () => handleConnectionStateChange(connection);

            const activeLocalStream = localStream;
            if (!activeLocalStream) {
                throw new Error('No se pudo acceder al micrófono.');
            }

            addLocalTracks(connection, activeLocalStream);

            const offer = await connection.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: false,
            });

            await connection.setLocalDescription(offer);

            const sdpResponse = await fetch(
                `https://api.openai.com/v1/realtime`,
                {
                    method: 'POST',
                    headers: {
                        Authorization: `Bearer ${sessionData.client_secret}`,
                        'Content-Type': 'application/sdp',
                    },
                    body: offer.sdp ?? '',
                },
            );

            if (!sdpResponse.ok) {
                const errorText = await sdpResponse.text();
                console.error('Error WebRTC de OpenAI:', errorText, 'Status:', sdpResponse.status);
                throw new Error(`OpenAI no aceptó la negociación de audio. Detalle: ${errorText}`);
            }

            const answerSdp = await sdpResponse.text();
            await connection.setRemoteDescription({ type: 'answer', sdp: answerSdp });

            playRemoteAudio();

            const duration = resolveSessionDuration(props.sessionDuration, sessionData);

            countdownManager.start({
                duration,
                onExpire: () => stopSession('Tiempo agotado. ¡Buen trabajo!'),
            });

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
    };

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

export type { VoiceChatProps } from './types';
