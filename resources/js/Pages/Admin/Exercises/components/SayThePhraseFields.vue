<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (!Array.isArray(props.form.solution)) props.form.solution = [];
</script>
<template>
    <div class="space-y-3">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-comment-dots text-gray-400"></i>
            Respuesta
        </div>
        <textarea v-model="form.solution[0]" rows="3"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            placeholder="Ingresa la respuesta"></textarea>
        <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
    </div>
</template>
