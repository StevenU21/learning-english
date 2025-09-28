<script setup>
import { reactive } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) {
    props.form.options = [];
}

function addOption() {
    if (props.form.options.length >= 4) return;
    props.form.options.push('');
}
function removeOption(idx) {
    props.form.options.splice(idx, 1);
}
function toggleSolution(idx) {
    const value = props.form.options[idx];
    if (!Array.isArray(props.form.solution)) props.form.solution = [];
    const i = props.form.solution.indexOf(String(value));
    if (i === -1) props.form.solution.push(String(value)); else props.form.solution.splice(i, 1);
}
function isChecked(opt) {
    if (!Array.isArray(props.form.solution)) return false;
    return props.form.solution.includes(String(opt));
}
</script>
<template>
    <div class="space-y-4">
        <h3 class="font-semibold text-sm">Opciones (máx 4)</h3>
        <div class="space-y-2">
            <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center space-x-2">
                <input v-model="form.options[idx]" type="text" class="input input-sm flex-1" placeholder="Opción" />
                <input type="checkbox" :checked="isChecked(opt)" @change="toggleSolution(idx)" class="h-4 w-4" />
                <button type="button" @click="removeOption(idx)"
                    class="text-xs text-red-500 hover:underline">Quitar</button>
            </div>
        </div>
        <div class="flex space-x-3">
            <button type="button" @click="addOption" :disabled="form.options.length >= 4"
                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Agregar opción</button>
        </div>
        <p v-if="errors.options" class="text-xs text-red-500">{{ errors.options }}</p>
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
