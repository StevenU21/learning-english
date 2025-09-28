<script setup>
import { defineProps } from 'vue';
const props = defineProps({
    form: Object,
    levels: Array,
    submitText: {
        type: String,
        default: 'Guardar'
    },
    onSubmit: Function,
});
</script>

<template>
    <form @submit.prevent="onSubmit" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Nombre</label>
            <input v-model="form.name" type="text" class="w-full mt-1 p-2 border rounded" />
            <div v-if="form.errors.name" class="text-red-600">{{ form.errors.name }}</div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Descripción</label>
            <textarea v-model="form.description" class="w-full mt-1 p-2 border rounded"></textarea>
            <div v-if="form.errors.description" class="text-red-600">{{ form.errors.description }}</div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Tiempo Esperado</label>
            <input v-model="form.expected_time" type="text" class="w-full mt-1 p-2 border rounded" />
            <div v-if="form.errors.expected_time" class="text-red-600">{{ form.errors.expected_time }}</div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Nivel</label>
            <select v-model="form.level_id" class="w-full mt-1 p-2 border rounded">
                <option v-for="level in levels" :value="level.id" :key="level.id">{{ level.name }}</option>
            </select>
            <div v-if="form.errors.level_id" class="text-red-600">{{ form.errors.level_id }}</div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Imagen</label>
            <input @change="e => form.image = e.target.files[0]" type="file" class="w-full mt-1" />
            <div v-if="form.errors.image" class="text-red-600">{{ form.errors.image }}</div>
        </div>

        <div class="flex justify-end">
            <button type="submit" :disabled="form.processing"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ submitText }}
            </button>
        </div>
    </form>
</template>
