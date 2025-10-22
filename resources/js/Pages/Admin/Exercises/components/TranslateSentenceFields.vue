<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (!Array.isArray(props.form.solution)) props.form.solution = [];
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-language text-gray-400"></i>
                Oración a traducir
            </div>
            <textarea v-model="form.prompt" rows="2"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"
                placeholder="Escribe aquí la oración a traducir..."></textarea>
            <p v-if="errors.prompt" class="text-xs text-red-400 mt-1">{{ errors.prompt }}</p>
        </div>
        <div>
            <div class="flex items-center gap-2 text-gray-300 text-sm font-semibold">
                <i class="fa-solid fa-arrow-right-arrow-left text-gray-400"></i>
                Traducción esperada
            </div>
            <textarea v-model="form.solution[0]" rows="2"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"
                placeholder="Escribe aquí la traducción esperada..."></textarea>
            <p v-if="errors.solution" class="text-xs text-red-400 mt-1">{{ errors.solution }}</p>
        </div>
    </div>
</template>
