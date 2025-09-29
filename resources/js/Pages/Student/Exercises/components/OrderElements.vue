<script setup>
import { ref } from 'vue';
import { VueDraggableNext as draggable } from 'vue-draggable-next';
const props = defineProps({ exercise: Object, showFeedback: Boolean });
const emit = defineEmits(['answered']);
const currentOrder = ref([...(props.exercise.options || [])]);


function submit() {
    if (props.showFeedback) return;
    const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const correct = JSON.stringify(target) === JSON.stringify(currentOrder.value);
    emit('answered', correct, currentOrder.value);
}
</script>
<template>
    <div class="space-y-3">
        <draggable v-model="currentOrder" item-key="item" tag="ul" class="space-y-2" handle=".drag-handle">
            <li v-for="(item, idx) in currentOrder" :key="item"
                class="flex items-center bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded text-sm text-gray-800 dark:text-gray-200 cursor-grab active:cursor-grabbing">
                <i class="fa-solid fa-grip-vertical drag-handle mr-3 text-gray-500"></i>
                <span class="flex-1">{{ item }}</span>
            </li>
        </draggable>
        <button @click="submit"
            class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Confirmar</button>
    </div>
</template>
