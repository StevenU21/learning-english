<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

if (!Array.isArray(props.form.options)) props.form.options = [];
// Ensure solution array exists
if (!Array.isArray(props.form.solution)) props.form.solution = [];

// Helpers for matching definitions
function getConceptos() {
    return [...new Set(props.form.options.map(o => o.concepto).filter(v => v))];
}
function getDefiniciones() {
    return [...new Set(props.form.options.map(o => o.definicion).filter(v => v))];
}
function setMatch(concepto, definicion) {
    const idx = props.form.solution.findIndex(p => p.concepto === concepto);
    if (idx !== -1) props.form.solution[idx].definicion = definicion;
    else props.form.solution.push({ concepto, definicion });
}
function getSelectedDefinicion(concepto) {
    const pair = props.form.solution.find(p => p.concepto === concepto);
    return pair ? pair.definicion : '';
}

function addPair() {
    props.form.options.push({ concepto: '', definicion: '' });
}
function removePair(idx) {
    props.form.options.splice(idx, 1);
}
</script>
<template>
    <div class="space-y-5">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-shuffle text-gray-400"></i>
            Conceptos y Definiciones
        </div>
        <div class="space-y-3">
            <div v-for="(pair, idx) in form.options" :key="idx" class="grid grid-cols-2 gap-3 items-center">
                <input v-model="pair.concepto" type="text" placeholder="Concepto"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                <div class="flex items-center gap-2">
                    <input v-model="pair.definicion" type="text" placeholder="Definición"
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
        <!-- Selección de definición correcta -->
        <div v-if="getConceptos().length && getDefiniciones().length" class="mt-4 space-y-3">
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-check-double text-gray-400"></i>
                Selecciona definición para cada concepto
            </div>
            <div class="space-y-2">
                <div v-for="concepto in getConceptos()" :key="concepto" class="flex items-center gap-3">
                    <span class="text-gray-300">{{ concepto }}</span>
                    <span class="text-gray-300">→</span>
                    <select :value="getSelectedDefinicion(concepto)" @change="setMatch(concepto, $event.target.value)"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Selecciona definición</option>
                        <option v-for="def in getDefiniciones()" :key="def" :value="def">{{ def }}</option>
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
                <li v-for="(p, i) in form.solution" :key="i">{{ p.concepto }} → {{ p.definicion }}</li>
            </ul>
        </div>
    </div>
</template>
