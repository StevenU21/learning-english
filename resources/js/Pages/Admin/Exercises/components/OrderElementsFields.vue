<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function addElement() {
    props.form.options.push('');
}
function removeElement(idx) {
    props.form.options.splice(idx, 1);
}
function setSolutionOrder() {
    // Solution is a copy of current order
    props.form.solution = [...props.form.options];
}
</script>
<template>
    <div class="space-y-3">
        <h3 class="font-semibold text-sm">Elementos a ordenar</h3>
        <div class="space-y-2">
            <div v-for="(el, idx) in form.options" :key="idx" class="flex space-x-2 items-center">
                <input v-model="form.options[idx]" type="text" class="input input-sm flex-1" placeholder="Elemento" />
                <button type="button" @click="removeElement(idx)" class="text-xs text-red-500">Quitar</button>
            </div>
        </div>
        <div class="flex space-x-4">
            <button type="button" @click="addElement"
                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Agregar</button>
            <button type="button" @click="setSolutionOrder"
                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Usar orden actual como solución</button>
        </div>
        <p v-if="errors.options" class="text-xs text-red-500">{{ errors.options }}</p>
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
