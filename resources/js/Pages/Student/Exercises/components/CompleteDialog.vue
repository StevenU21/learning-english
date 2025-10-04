<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);

// Expect options: array of possible phrases, solution: array of correct phrases
const selected = ref([]);

function toggle(option) {
    if (props.showFeedback) return;
    const idx = selected.value.indexOf(option);
    if (idx >= 0) selected.value.splice(idx, 1); else selected.value.push(option);
}

function submit() {
    if (props.showFeedback) return;
    const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const correct = JSON.stringify([...selected.value].sort()) === JSON.stringify([...target].sort());
    emit('answered', correct, selected.value);
}
</script>
<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-2">
            <button v-for="opt in props.exercise.options" :key="opt" @click="toggle(opt)"
                :class="['px-3 py-1.5 rounded-full text-sm border', selected.includes(opt) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200']">
                {{ opt }}
            </button>
        </div>
        <PrimaryButton @click="submit" class="w-full">
            <i class="fa-solid fa-check mr-2"></i> Comprobar
        </PrimaryButton>
    </div>
</template>
