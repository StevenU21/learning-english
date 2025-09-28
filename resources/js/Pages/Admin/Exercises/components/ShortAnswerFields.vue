<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (!Array.isArray(props.form.solution)) props.form.solution = [];
</script>
<template>
    <div class="space-y-2">
        <h3 class="font-semibold text-sm">Respuesta</h3>
        <textarea v-model="form.solution[0]" rows="3" class="input w-full"
            placeholder="Ingresa la respuesta"></textarea>
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
