<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    defaultVoice: { type: String, default: 'alloy' },
    defaultModel: { type: String, default: 'gpt-4o-realtime-preview-2024-12-17' },
    sessionDuration: { type: Number, default: 60 },
});

const statusMessage = ref('Listo para iniciar tu práctica de conversación.');
const isConnecting = ref(false);
const isActive = ref(false);
const countdown = ref(props.sessionDuration);
const errorMessage = ref('');
const remoteAudioEl = ref(null);

let peerConnection = null;
let localStream = null;
let remoteStream = null;
let countdownInterval = null;
let sessionTimeout = null;

const csrfToken = computed(() => {
    const token = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        throw new Error('No se encontró el token CSRF en el documento.');
    }
    return token;
});

const resetCountdown = () => {
    clearInterval(countdownInterval);
    clearTimeout(sessionTimeout);
    countdownInterval = null;
    sessionTimeout = null;
    countdown.value = props.sessionDuration;
};

const cleanupMedia = () => {
    if (peerConnection) {
        try {
            peerConnection.getSenders().forEach((sender) => peerConnection.removeTrack(sender));
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

    resetCountdown();
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

const startCountdown = (duration) => {
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
};

const mapMediaError = (error) => {
    if (error?.name === 'NotAllowedError' || error?.name === 'SecurityError') {
        return 'Necesitamos permisos del micrófono para iniciar la sesión.';
    }

    if (error?.name === 'NotFoundError') {
        return 'No encontramos un micrófono disponible en este dispositivo.';
    }

    if (error?.name === 'NotReadableError') {
        return 'Otra aplicación está usando el micrófono. Ciérrala e inténtalo de nuevo.';
    }

    if (error?.message === 'secure-context-required') {
        return 'Necesitamos que abras la aplicación mediante HTTPS o desde localhost para usar el micrófono.';
    }

    return error?.message ?? 'Ocurrió un error inesperado al capturar el audio.';
};

const getMicrophoneStream = async () => {
    const constraints = {
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

    const legacyGetUserMedia =
        navigator?.getUserMedia ||
        navigator?.webkitGetUserMedia ||
        navigator?.mozGetUserMedia ||
        navigator?.msGetUserMedia;

    if (legacyGetUserMedia) {
        return new Promise((resolve, reject) => {
            legacyGetUserMedia.call(navigator, constraints, resolve, reject);
        });
    }

    throw new Error('Tu navegador no soporta captura de audio.');
};

const startSession = async () => {
    if (isConnecting.value || isActive.value) {
        return;
    }

    errorMessage.value = '';
    statusMessage.value = 'Preparando tu sesión de voz...';
    isConnecting.value = true;

    try {
        localStream = await getMicrophoneStream();

        statusMessage.value = 'Conectando con la IA...';

        const sessionResponse = await fetch(route('student.voice-chat.session'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ voice: props.defaultVoice }),
        });

        if (!sessionResponse.ok) {
            const problem = await sessionResponse.json().catch(() => ({}));
            throw new Error(problem.message ?? 'No se pudo crear la sesión en el servidor.');
        }

        const sessionData = await sessionResponse.json();

        const connection = new RTCPeerConnection({
            iceServers: [],
        });

        peerConnection = connection;
        remoteStream = new MediaStream();
        if (remoteAudioEl.value) {
            remoteAudioEl.value.srcObject = remoteStream;
        }

        connection.ontrack = (event) => {
            event.track && remoteStream.addTrack(event.track);
        };

        connection.onconnectionstatechange = () => {
            if (['failed', 'disconnected', 'closed'].includes(connection.connectionState)) {
                stopSession('La conexión se cerró.');
            }
        };

        localStream.getTracks().forEach((track) => {
            connection.addTrack(track, localStream);
        });

        const offer = await connection.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: false });
        await connection.setLocalDescription(offer);

        const sdpResponse = await fetch(`https://api.openai.com/v1/realtime?model=${encodeURIComponent(sessionData.model)}`, {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${sessionData.client_secret}`,
                'Content-Type': 'application/sdp',
                'OpenAI-Beta': 'realtime=v1',
            },
            body: offer.sdp,
        });

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

        startCountdown(sessionData.expires_in ?? props.sessionDuration);

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
</script>

<template>

    <Head title="Chat de voz" />

    <StudentLayout>
        <template #header>
            <PageHeader title="Chat de voz" subtitle="Practica tu inglés con una conversación guiada por IA."
                icon="fa-solid fa-microphone-lines" :breadcrumbs="[
                    { label: 'Inicio', href: route('student.units.index'), icon: 'fa-solid fa-house' },
                    { label: 'Chat de voz' }
                ]" gradient-classes="from-sky-600 to-indigo-600" />
        </template>
        <div
            class="flex min-h-[calc(100vh-56px)] flex-col bg-gradient-to-b from-sky-100 via-white to-white dark:from-gray-900 dark:via-gray-900 dark:to-gray-950 pb-24 sm:pb-8">
            <div class="flex flex-1 flex-col items-center justify-center px-6 pt-8 sm:pt-12">
                <div
                    class="w-full max-w-md rounded-3xl bg-white/80 p-6 text-center shadow-lg backdrop-blur dark:bg-gray-900/70 dark:text-gray-100">
                    <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl dark:text-white">Chat de voz con IA</h1>
                    <p class="mt-3 text-sm text-gray-600 sm:text-base dark:text-gray-300">
                        Practica conversaciones en voz con sesiones de un minuto. Asegúrate de estar en un lugar
                        tranquilo y usa audífonos si es posible.
                    </p>

                    <div class="mt-6 rounded-2xl bg-sky-50 px-4 py-5 text-left dark:bg-sky-500/10">
                        <p class="text-sm font-medium uppercase tracking-wide text-sky-500 dark:text-sky-300">Estado</p>
                        <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{ statusMessage }}</p>
                        <p v-if="isActive" class="mt-3 text-2xl font-semibold text-sky-600 dark:text-sky-400">
                            00:{{ countdown.toString().padStart(2, '0') }}
                        </p>
                    </div>

                    <div v-if="errorMessage"
                        class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-left text-sm text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-200">
                        {{ errorMessage }}
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <button type="button"
                            class="inline-flex w-full items-center justify-center rounded-full bg-sky-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:bg-sky-300 dark:bg-sky-500 dark:hover:bg-sky-400 dark:disabled:bg-sky-500/40"
                            :disabled="isConnecting || isActive" @click="startSession">
                            <span v-if="isConnecting" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner animate-spin"></i>
                                Conectando...
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-microphone"></i>
                                Iniciar sesión
                            </span>
                        </button>
                        <button v-if="isActive" type="button"
                            class="inline-flex w-full items-center justify-center rounded-full border border-gray-200 px-6 py-3 text-base font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                            @click="stopSession('Sesión finalizada por ti.')">
                            <i class="fa-solid fa-phone-slash mr-2"></i>
                            Finalizar
                        </button>
                    </div>

                    <div
                        class="mt-6 rounded-2xl bg-gray-50 px-4 py-4 text-left text-xs text-gray-500 sm:text-sm dark:bg-gray-800/80 dark:text-gray-300">
                        <p class="font-medium text-gray-600 dark:text-gray-200">Recomendaciones:</p>
                        <ul class="mt-2 space-y-2">
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-wave-square mt-0.5 text-sky-500 dark:text-sky-300"></i>
                                Habla con claridad y espera a que la IA termine antes de volver a preguntar.
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-headphones mt-0.5 text-sky-500 dark:text-sky-300"></i>
                                Usa audífonos para reducir eco y mejorar el reconocimiento de voz.
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-rotate-left mt-0.5 text-sky-500 dark:text-sky-300"></i>
                                Puedes iniciar otra sesión cuando el tiempo termine.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <audio ref="remoteAudioEl" autoplay playsinline class="hidden" />
        </div>
    </StudentLayout>
</template>
