<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function addPhrase() {
    props.form.options.push('');
}
function removePhrase(idx) {
    props.form.options.splice(idx, 1);
}
function addSolutionLine() {
    props.form.solution.push('');
}
function removeSolutionLine(idx) {
    props.form.solution.splice(idx, 1);
}
</script>
<template>
    <div class="space-y-4">
        <h3 class="font-semibold text-sm">Frases disponibles</h3>
        <div class="space-y-2">
            <div v-for="(phrase, idx) in form.options" :key="idx" class="flex items-center space-x-2">
                <input v-model="form.options[idx]" type="text" class="input input-sm flex-1" placeholder="Frase" />
                <button type="button" @click="removePhrase(idx)" class="text-xs text-red-500">Quitar</button>
            </div>
            <button type="button" @click="addPhrase"
                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Agregar frase</button>
        </div>

        <h3 class="font-semibold text-sm pt-2">Solución (orden correcto)</h3>
        <div class="space-y-2">
            <div v-for="(s, idx) in form.solution" :key="idx" class="flex items-center space-x-2">
                <input v-model="form.solution[idx]" type="text" class="input input-sm flex-1"
                    placeholder="Frase correcta" />
                <button type="button" @click="removeSolutionLine(idx)" class="text-xs text-red-500">Quitar</button>
            </div>
            <button type="button" @click="addSolutionLine"
                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Agregar línea solución</button>
        </div>
        <p v-if="errors.options" class="text-xs text-red-500">{{ errors.options }}</p>
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
