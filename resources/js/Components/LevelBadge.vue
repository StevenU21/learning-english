<template>
    <span :class="badgeClass">
        {{ level }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    level: { type: String, required: true }
});

// Paleta de colores para badges de nivel
const colorPalette = [
    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300',
    'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
    'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300'
];

// Función para obtener un hash consistente de la cadena
function hashString(str: string) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = (hash << 5) - hash + str.charCodeAt(i);
        hash |= 0;
    }
    return Math.abs(hash);
}

const badgeClass = computed(() => {
    const base = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold';
    const idx = hashString(props.level) % colorPalette.length;
    return base + ' ' + colorPalette[idx];
});
</script>
