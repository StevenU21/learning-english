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
  <div class="space-y-5">
    <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
      <i class="fa-solid fa-link text-gray-400"></i>
      Pares a relacionar
    </div>
    <div class="space-y-3">
      <div v-for="(pair, idx) in form.options" :key="idx" class="grid grid-cols-2 gap-3 items-center">
        <input v-model="pair.left" type="text" placeholder="Columna A" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
        <div class="flex items-center gap-2">
          <input v-model="pair.right" type="text" placeholder="Columna B" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
          <button type="button" @click="removePair(idx)" class="text-xs text-red-400 hover:text-red-300">Quitar</button>
        </div>
      </div>
    </div>
    <button type="button" @click="addPair" class="px-3 py-1.5 text-xs rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
      <i class="fa-solid fa-plus mr-1"></i> Agregar par
    </button>
    <p v-if="errors.options" class="text-xs text-red-400">{{ errors.options }}</p>
  </div>
</template>
