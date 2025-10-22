<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (typeof props.form.solution !== 'string') props.form.solution = '';
</script>
<template>
    <div class="space-y-3">
        <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
            <i class="fa-solid fa-microphone-lines text-gray-400"></i>
            Frase esperada (solución)
        </div>
        <textarea v-model="form.solution" rows="2"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            placeholder="Ingresa la frase que el usuario debe decir exactamente"></textarea>
        <p v-if="errors.solution" class="text-xs text-red-400">{{ errors.solution }}</p>
    </div>
</template>
