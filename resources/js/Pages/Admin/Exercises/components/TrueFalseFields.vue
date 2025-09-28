<script setup>
const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) }
});

const options = ['True', 'False'];
if (!Array.isArray(props.form.solution)) props.form.solution = [];

function select(value) {
  props.form.solution = [value];
}
</script>
<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
      <i class="fa-solid fa-check-double text-gray-400"></i>
      Selecciona la respuesta correcta
    </div>
    <div class="flex gap-4 flex-wrap">
      <button
        type="button"
        v-for="opt in options"
        :key="opt"
        @click="select(opt)"
        :class="[
          'px-5 py-2 rounded-md border text-sm font-medium transition',
          form.solution[0] === opt
            ? 'bg-indigo-600 border-indigo-600 text-white shadow'
            : 'bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'
        ]"
      >
        {{ opt }}
      </button>
    </div>
    <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
  </div>
</template>
