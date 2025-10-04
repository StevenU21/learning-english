<script setup>

import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);
const inputVal = ref('');
const isDisabled = ref(false);

function submit() {
    if (props.showFeedback || isDisabled.value) return;
    const solution = Array.isArray(props.exercise.solution) ? props.exercise.solution.map(s => String(s).trim().toLowerCase()) : [];
    const correct = solution.includes(inputVal.value.trim().toLowerCase());
    emit('answered', correct, inputVal.value);
    isDisabled.value = true;
}
</script>
<template>
    <div class="space-y-3">
        <input v-model="inputVal" type="text"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm"
            placeholder="Escribe tu respuesta" />
        <button @click="submit" :disabled="isDisabled"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </button>
    </div>
</template>
