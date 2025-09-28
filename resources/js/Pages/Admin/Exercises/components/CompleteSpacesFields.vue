<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});
if (!Array.isArray(props.form.solution)) props.form.solution = [];
</script>
<template>
    <div class="space-y-2">
        <h3 class="font-semibold text-sm">Solución</h3>
        <input v-model="form.solution[0]" type="text" class="input w-full" placeholder="Ingresa la solución" />
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
