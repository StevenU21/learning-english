<template>
    <div class="flex flex-col items-center w-full">
        <div
            class="flex items-center gap-4 w-full max-w-xs bg-gray-100 dark:bg-gray-800 rounded-xl shadow px-4 py-3 border border-indigo-200 dark:border-gray-700">
            <button @click="togglePlay" :class="['rounded-full flex items-center justify-center shadow transition-all duration-200',
                isPlaying ? 'bg-indigo-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-indigo-400',
            ]" style="width: 48px; height: 48px; font-size: 1.5rem;">
                <i :class="isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play'" />
            </button>
            <div class="flex-1 grid grid-cols-[auto_1fr_auto] items-center gap-2 w-full">
                <span class="text-xs text-gray-600 dark:text-gray-300 font-mono">{{ formatTime(currentTime) }}</span>
                <input type="range" min="0" :max="duration" step="0.01" v-model="currentTime" @input="seek"
                    class="w-full h-2 rounded-lg appearance-none accent-gray-400 bg-gray-300 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400" />
                <span class="text-xs text-gray-600 dark:text-gray-300 font-mono text-right">{{ formatTime(duration)
                    }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    src: { type: String, required: true }
});


const audio = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);

function formatTime(sec) {
    if (!isFinite(sec)) return '0:00';
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function togglePlay() {
    if (!audio.value) return;
    if (isPlaying.value) {
        audio.value.pause();
    } else {
        audio.value.play();
    }
}

function seek(e) {
    if (audio.value) {
        audio.value.currentTime = parseFloat(currentTime.value);
    }
}

onMounted(() => {
    audio.value = new Audio(props.src);
    audio.value.addEventListener('loadedmetadata', () => {
        duration.value = audio.value.duration;
    });
    audio.value.addEventListener('timeupdate', () => {
        currentTime.value = audio.value.currentTime;
    });
    audio.value.addEventListener('play', () => {
        isPlaying.value = true;
    });
    audio.value.addEventListener('pause', () => {
        isPlaying.value = false;
    });
    audio.value.addEventListener('ended', () => {
        isPlaying.value = false;
        currentTime.value = 0;
    });
});

onUnmounted(() => {
    if (audio.value) {
        audio.value.pause();
        audio.value.src = '';
        audio.value = null;
    }
});

watch(() => props.src, (newSrc) => {
    if (audio.value) {
        audio.value.pause();
        audio.value.src = newSrc;
        audio.value.load();
        currentTime.value = 0;
        isPlaying.value = false;
    }
});
</script>
