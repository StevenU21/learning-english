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
  props.form.solution = [...props.form.options];
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
        <input v-model="form.options[idx]" type="text" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Elemento" />
        <button type="button" @click="removeElement(idx)" class="text-xs text-red-400 hover:text-red-300">Quitar</button>
      </div>
    </div>
    <div class="flex flex-wrap gap-3">
      <button type="button" @click="addElement" class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
        <i class="fa-solid fa-plus mr-1"></i> Agregar
      </button>
      <button type="button" @click="setSolutionOrder" class="px-3 py-1.5 text-xs rounded-md bg-indigo-600 text-white hover:bg-indigo-500">
        <i class="fa-solid fa-check mr-1"></i> Usar orden actual como solución
      </button>
    </div>
    <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>
    <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
  </div>
</template>
