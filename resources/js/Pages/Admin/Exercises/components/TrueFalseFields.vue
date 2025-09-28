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
    <div class="space-y-2">
        <h3 class="font-semibold text-sm">Selecciona la respuesta correcta</h3>
        <div class="flex space-x-4">
            <button type="button" v-for="opt in options" :key="opt" @click="select(opt)"
                :class="['px-4 py-2 rounded border', form.solution[0] === opt ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-700']">{{
                opt }}</button>
        </div>
        <p v-if="errors.solution" class="text-xs text-red-500">{{ errors.solution }}</p>
    </div>
</template>
