<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function addPair() {
    props.form.options.push({ left: '', right: '' });
}
function removePair(idx) {
    props.form.options.splice(idx, 1);
}
</script>
<template>
    <div class="space-y-4">
        <h3 class="font-semibold text-sm">Pares a relacionar</h3>
        <div class="space-y-3">
            <div v-for="(pair, idx) in form.options" :key="idx" class="grid grid-cols-2 gap-2 items-center">
                <input v-model="pair.left" type="text" placeholder="Columna A" class="input input-sm" />
                <div class="flex space-x-2 items-center">
                    <input v-model="pair.right" type="text" placeholder="Columna B" class="input input-sm flex-1" />
                    <button type="button" @click="removePair(idx)" class="text-xs text-red-500">Quitar</button>
                </div>
            </div>
        </div>
        <button type="button" @click="addPair" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Agregar
            par</button>
        <p v-if="errors.options" class="text-xs text-red-500">{{ errors.options }}</p>
    </div>
</template>
