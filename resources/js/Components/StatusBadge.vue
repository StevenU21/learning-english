<template>
    <span :class="['px-2 py-1 rounded text-xs font-medium', badgeClasses]">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true }
});

// Mapeo de estilos y etiquetas por estado
const statusStyles = {
    no_comenzado: {
        label: 'Pendiente',
        classes: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 ring-1 ring-yellow-200 dark:ring-yellow-800'
    },
    en_progreso: {
        label: 'En Progreso',
        classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800'
    },
    completado: {
        label: 'Completado',
        classes: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800'
    }
};

function capitalizeLabel(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
}

const badgeProps = computed(() => {
    return statusStyles[props.status] || {
        label: capitalizeLabel(props.status),
        classes: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-gray-800'
    };
});

const label = computed(() => badgeProps.value.label);
const badgeClasses = computed(() => badgeProps.value.classes);
</script>
