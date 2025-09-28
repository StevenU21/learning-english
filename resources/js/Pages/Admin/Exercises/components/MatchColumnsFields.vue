<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
// Ensure solution array exists
if (!Array.isArray(props.form.solution)) props.form.solution = [];

import { watch } from 'vue';
watch(() => props.form.options, val => console.log('Opciones:', JSON.stringify(val)), { deep: true });
watch(() => props.form.solution, val => console.log('Solución:', JSON.stringify(val)), { deep: true });

function getLeftValues() {
    return [...new Set(props.form.options.map(o => o.left).filter(v => v))];
}
function getRightValues() {
    return [...new Set(props.form.options.map(o => o.right).filter(v => v))];
}
function setMatch(left, right) {
    const idx = props.form.solution.findIndex(p => p.left === left);
    if (idx !== -1) props.form.solution[idx].right = right;
    else props.form.solution.push({ left, right });
}
function getSelectedRight(left) {
    const pair = props.form.solution.find(p => p.left === left);
    return pair ? pair.right : '';
}

function addPair() {
    props.form.options.push({ left: '', right: '' });
}
function removePair(idx) {
    props.form.options.splice(idx, 1);
}
</script>
<template>
    <div class="space-y-5">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-link text-gray-400"></i>
            Pares a relacionar
        </div>
        <div class="space-y-3">
            <div v-for="(pair, idx) in form.options" :key="idx" class="grid grid-cols-2 gap-3 items-center">
                <input v-model="pair.left" type="text" placeholder="Columna A"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                <div class="flex items-center gap-2">
                    <input v-model="pair.right" type="text" placeholder="Columna B"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                    <button type="button" @click="removePair(idx)"
                        class="text-xs text-red-400 hover:text-red-300">Quitar</button>
                </div>
            </div>
        </div>
        <button type="button" @click="addPair"
            class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
            <i class="fa-solid fa-plus mr-1"></i> Agregar par
        </button>
        <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>
        <!-- Selección de solución -->
        <div v-if="getLeftValues().length && getRightValues().length" class="mt-4 space-y-3">
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-check-double text-gray-400"></i>
                Selecciona los matches correctos
            </div>
            <div class="space-y-2">
                <div v-for="left of getLeftValues()" :key="left" class="flex items-center gap-3">
                    <span class="text-gray-300">{{ left }}</span>
                    <span class="text-gray-300">→</span>
                    <select :value="getSelectedRight(left)" @change="setMatch(left, $event.target.value)"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Selecciona</option>
                        <option v-for="right of getRightValues()" :key="right" :value="right">{{ right }}</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Vista previa de la solución -->
        <div v-if="form.solution.length" class="mt-4">
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-eye text-gray-400"></i>
                Vista previa de la solución
            </div>
            <ul class="list-disc list-inside text-gray-300">
                <li v-for="(p, i) in form.solution" :key="i">{{ p.left }} → {{ p.right }}</li>
            </ul>
        </div>
    </div>
</template>
