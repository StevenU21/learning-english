<script setup>
import { ref, watch } from 'vue';
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
// Ensure solution array and initialize when options change
if (!Array.isArray(props.form.solution)) props.form.solution = [];
// Sync solution to reflect options changes
watch(() => props.form.options, (opts) => {
    props.form.solution = Array.isArray(opts) ? opts.slice() : [];
}, { deep: true });

function addElement() {
    props.form.options.push('');
}
function removeElement(idx) {
    props.form.options.splice(idx, 1);
}

const dragIndex = ref(null);
function onDragStart(idx) {
    dragIndex.value = idx;
}
function onDrop(idx) {
    if (dragIndex.value === null || dragIndex.value === idx) return;
    const moved = props.form.solution.splice(dragIndex.value, 1)[0];
    props.form.solution.splice(idx, 0, moved);
    dragIndex.value = null;
}
</script>
<template>
    <div class="space-y-5">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-arrow-down-short-wide text-gray-400"></i>
            Elementos a ordenar
        </div>
        <div class="space-y-3">
            <div v-for="(el, idx) in form.options" :key="idx" class="flex items-center gap-3">
                <input v-model="form.options[idx]" type="text"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    placeholder="Elemento" />
                <button type="button" @click="removeElement(idx)"
                    class="text-xs text-red-400 hover:text-red-300">Quitar</button>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" @click="addElement"
                class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                <i class="fa-solid fa-plus mr-1"></i> Agregar
            </button>
        </div>
        <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>


        <!-- Selector de solución con drag-and-drop -->
        <div class="mt-4">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <i class="fa-solid fa-arrows-up-down-left-right"></i>
                Arrastra y suelta para reordenar
            </div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Solución (orden correcto)</label>
            <div v-if="form.solution.length" class="space-y-2">
                <div v-for="(item, idx) in form.solution" :key="'sol-' + idx" draggable="true"
                    @dragstart="onDragStart(idx)" @dragover.prevent @drop="onDrop(idx)"
                    class="flex items-center gap-2 p-2 rounded-md bg-gray-800 dark:bg-gray-700 text-gray-300 cursor-grab hover:bg-gray-700 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-grip-lines"></i>
                    <span>{{ item }}</span>
                </div>
            </div>
            <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
        </div>
    </div>
</template>
