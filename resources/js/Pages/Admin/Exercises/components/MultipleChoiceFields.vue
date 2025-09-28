<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function addOption() {
    if (props.form.options.length >= 4) return;
    props.form.options.push('');
}
function removeOption(idx) {
    props.form.options.splice(idx, 1);
}
function toggleSolution(idx) {
    const value = props.form.options[idx];
    const valStr = String(value);
    const i = props.form.solution.indexOf(valStr);
    if (i === -1) props.form.solution.push(valStr); else props.form.solution.splice(i, 1);
}
function isChecked(opt) {
    return props.form.solution.includes(String(opt));
}
</script>
<template>
    <div class="space-y-5">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-list text-gray-400"></i>
            Opciones (máx 4)
        </div>
        <div class="space-y-3">
            <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-3">
                <input v-model="form.options[idx]" type="text"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    placeholder="Opción" />
                <label class="flex items-center gap-1 text-xs text-gray-300">
                    <input type="checkbox" :checked="isChecked(opt)" @change="toggleSolution(idx)"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" />
                    Correcta
                </label>
                <button type="button" @click="removeOption(idx)"
                    class="text-xs text-red-400 hover:text-red-300">Quitar</button>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="addOption" :disabled="form.options.length >= 4"
                class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                <i class="fa-solid fa-plus mr-1"></i> Agregar opción
            </button>
        </div>
        <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>
        <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
    </div>
</template>
