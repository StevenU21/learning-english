import type { Ref } from 'vue';

type AudioKind = 'local' | 'remote';

type AudioAnalyserManagerOptions = {
    localLevel: Ref<number>;
    remoteLevel: Ref<number>;
    localSpectrum: Ref<number[]>;
    remoteSpectrum: Ref<number[]>;
};

const SPECTRUM_BUCKETS = 12;

function buildSpectrum(data: Uint8Array) {
    const spectrum = new Array<number>(SPECTRUM_BUCKETS).fill(0);
    const bucketSize = Math.max(1, Math.floor(data.length / SPECTRUM_BUCKETS));

    for (let bucket = 0; bucket < SPECTRUM_BUCKETS; bucket += 1) {
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

export const createAudioAnalyserManager = ({
    localLevel,
    remoteLevel,
    localSpectrum,
    remoteSpectrum,
}: AudioAnalyserManagerOptions) => {
    const audioContexts: Partial<Record<AudioKind, AudioContext>> = {};
    const audioAnalysers: Partial<Record<AudioKind, AnalyserNode>> = {};
    const audioSources: Partial<Record<AudioKind, MediaStreamAudioSourceNode>> = {};

    let visualizerFrame: number | null = null;

    const resetLevels = (kind: AudioKind) => {
        if (kind === 'local') {
            localLevel.value = 0;
            localSpectrum.value = Array(SPECTRUM_BUCKETS).fill(0);
            return;
        }

        remoteLevel.value = 0;
        remoteSpectrum.value = Array(SPECTRUM_BUCKETS).fill(0);
    };

    const updateLevels = (kind: AudioKind) => {
        const analyser = audioAnalysers[kind];

        if (!analyser) {
            resetLevels(kind);
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
            return;
        }

        remoteLevel.value = level;
        remoteSpectrum.value = buildSpectrum(frequencyData);
    };

    const ensureVisualizerRunning = () => {
        if (visualizerFrame !== null) {
            return;
        }

        const loop = () => {
            updateLevels('local');
            updateLevels('remote');
            visualizerFrame = window.requestAnimationFrame(loop);
        };

        visualizerFrame = window.requestAnimationFrame(loop);
    };

    const stopVisualizer = () => {
        if (visualizerFrame === null) {
            return;
        }

        window.cancelAnimationFrame(visualizerFrame);
        visualizerFrame = null;
    };

    const detach = (kind: AudioKind) => {
        audioSources[kind]?.disconnect();
        audioAnalysers[kind]?.disconnect();
        audioContexts[kind]?.close().catch(() => undefined);

        delete audioSources[kind];
        delete audioAnalysers[kind];
        delete audioContexts[kind];

        resetLevels(kind);
    };

    const attach = (kind: AudioKind, stream: MediaStream | null) => {
        if (!stream) {
            detach(kind);
            return;
        }

        detach(kind);

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
            resetLevels(kind);
        }
    };

    const cleanup = () => {
        detach('local');
        detach('remote');
        stopVisualizer();
    };

    return {
        attach,
        detach,
        cleanup,
    } as const;
};
