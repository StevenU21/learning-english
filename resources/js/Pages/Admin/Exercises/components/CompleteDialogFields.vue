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
  <div class="space-y-6">
    <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
      <i class="fa-solid fa-comments text-gray-400"></i>
      Frases disponibles
    </div>
    <div class="space-y-3">
      <div v-for="(phrase, idx) in form.options" :key="idx" class="flex items-center gap-3">
        <input v-model="form.options[idx]" type="text" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Frase" />
        <button type="button" @click="removePhrase(idx)" class="text-xs text-red-400 hover:text-red-300">Quitar</button>
      </div>
      <button type="button" @click="addPhrase" class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
        <i class="fa-solid fa-plus mr-1"></i> Agregar frase
      </button>
    </div>

    <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold pt-2">
      <i class="fa-solid fa-clipboard-check text-gray-400"></i>
      Solución (orden correcto)
    </div>
    <div class="space-y-3">
      <div v-for="(s, idx) in form.solution" :key="idx" class="flex items-center gap-3">
        <input v-model="form.solution[idx]" type="text" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Frase correcta" />
        <button type="button" @click="removeSolutionLine(idx)" class="text-xs text-red-400 hover:text-red-300">Quitar</button>
      </div>
      <button type="button" @click="addSolutionLine" class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
        <i class="fa-solid fa-plus mr-1"></i> Agregar línea solución
      </button>
    </div>
    <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>
    <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
  </div>
</template>
