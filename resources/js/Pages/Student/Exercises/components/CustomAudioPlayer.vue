<template>
    <div class="flex flex-col items-center w-full">
        <div
            class="flex items-center gap-4 w-full max-w-xs bg-gray-100 dark:bg-gray-800 rounded-xl shadow px-4 py-3 border border-indigo-200 dark:border-gray-700">
            <button @click="togglePlay" @mouseover="hovering = true" @mouseleave="hovering = false" :class="['rounded-full flex items-center justify-center shadow transition-all duration-200',
                isPlaying ? 'bg-indigo-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
            ]" :style="!isPlaying && hovering ? 'background: #6d6eea; color: #fff; transition: background 0.2s;' : ''"
                style="width: 48px; height: 48px; font-size: 1.5rem;">
                <i :class="isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play'" />
            </button>
            <div class="flex-1 grid grid-cols-[auto_1fr_auto] items-center gap-2 w-full">
                <span class="text-xs text-gray-600 dark:text-gray-300 font-mono">{{ formatTime(currentTime) }}</span>
                <div class="w-full">
                    <div ref="waveformRef" class="w-full" style="height: 48px;"></div>
                </div>
                <span class="text-xs text-gray-600 dark:text-gray-300 font-mono text-right">{{ formatTime(duration)
                }}</span>
            </div>
        </div>
        <div class="flex gap-2 mb-2 mt-2">
            <button v-for="opt in rateOptions" :key="opt.value" type="button" @click="setPlaybackRate(opt.value)"
                :class="[
                    'transition-colors duration-150',
                    playbackRate === opt.value
                        ? 'bg-indigo-600 text-white border-indigo-700 ring-2 ring-indigo-400'
                        : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700',
                    'border-2',
                    'px-4 py-2 rounded-lg font-semibold',
                ]">
                {{ opt.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import WaveSurfer from 'wavesurfer.js';

const hovering = ref(false);
const props = defineProps({
    src: { type: String, required: true },
    playbackRate: { type: Number, default: 1 },
    rateOptions: {
        type: Array, default: () => [
            { label: 'Normal', value: 1 },
            { label: 'Lento', value: 0.6 }
        ]
    },
});

const waveformRef = ref(null);
const wavesurfer = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const playbackRate = ref(props.playbackRate);
const audioKey = ref(0); // para forzar recarga

function resetState() {
    playbackRate.value = props.playbackRate;
    isPlaying.value = false;
    currentTime.value = 0;
    duration.value = 0;
}


function formatTime(sec) {
    if (!isFinite(sec)) return '0:00';
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function togglePlay() {
    if (!wavesurfer.value) return;
    if (isPlaying.value) {
        wavesurfer.value.pause();
        // Al pausar, la próxima vez que se reproduzca, volverá al inicio
        wavesurfer.value.seekTo(0);
        currentTime.value = 0;
    } else {
        wavesurfer.value.play();
    }
}

function setPlaybackRate(rate) {
    playbackRate.value = rate;
    if (wavesurfer.value) {
        wavesurfer.value.setPlaybackRate(rate);
    }
    audioKey.value++;
}

onMounted(() => {
    resetState();
    wavesurfer.value = WaveSurfer.create({
        container: waveformRef.value,
        waveColor: '#a5b4fc',
        progressColor: '#6366f1',
        height: 48,
        barWidth: 2,
        barRadius: 2,
        responsive: true,
        cursorColor: '#6366f1',
        backend: 'mediaelement',
    });
    wavesurfer.value.load(props.src);
    wavesurfer.value.setPlaybackRate(playbackRate.value);

    wavesurfer.value.on('ready', () => {
        duration.value = wavesurfer.value.getDuration();
    });
    wavesurfer.value.on('audioprocess', () => {
        currentTime.value = wavesurfer.value.getCurrentTime();
    });
    wavesurfer.value.on('seek', () => {
        currentTime.value = wavesurfer.value.getCurrentTime();
    });
    wavesurfer.value.on('play', () => {
        isPlaying.value = true;
    });
    wavesurfer.value.on('pause', () => {
        isPlaying.value = false;
    });
    wavesurfer.value.on('finish', () => {
        isPlaying.value = false;
        currentTime.value = 0;
    });
});

onUnmounted(() => {
    if (wavesurfer.value) {
        wavesurfer.value.destroy();
        wavesurfer.value = null;
    }
});

watch(() => props.src, (newSrc) => {
    if (wavesurfer.value) {
        wavesurfer.value.load(newSrc);
        resetState();
    }
});

watch(() => props.playbackRate, (newRate) => {
    setPlaybackRate(newRate);
});
</script>
