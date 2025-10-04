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
        <PrimaryButton @click="submit" :disabled="isDisabled" class="w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </PrimaryButton>
    </div>
</template>
