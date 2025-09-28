<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    exercises: [Object, Array],
    permissions: Object,
    filters: Object,
    types: Array,
    lessons: Array,
});

const { exercises, permissions, filters, types, lessons } = props;
const exerciseList = Array.isArray(exercises) ? exercises : (exercises.data ?? []);

const form = useForm({
    type: filters.type || '',
    lesson: filters.lesson || '',
});

function applyFilters() {
    form.get(route('exercises.index'), { preserveState: true, replace: true });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Ejercicios" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Ejercicios
                </h2>
                <div>
                    <Link v-if="permissions.create" :href="route('exercises.create')">
                        <PrimaryButton>
                            <i class="fa-solid fa-plus mr-2"></i> Agregar Ejercicio
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
            <div class="mt-4 flex space-x-4">
                <div>
                    <SelectInput v-model="form.type" @change="applyFilters">
                        <option value="">Todos los tipos</option>
                        <option v-for="type in types" :value="type.id" :key="type.id">{{ type.name }}</option>
                    </SelectInput>
                </div>
                <div>
                    <SelectInput v-model="form.lesson" @change="applyFilters">
                        <option value="">Todas las lecciones</option>
                        <option v-for="lesson in lessons" :value="lesson.id" :key="lesson.id">{{ lesson.name }}</option>
                    </SelectInput>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max">
                            <thead class="bg-gray-200 dark:bg-gray-900">
                                <tr>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">ID</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Tipo</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Lección</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Prompt</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="exerciseList.length === 0">
                                    <td colspan="5" class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron ejercicios.
                                    </td>
                                </tr>
                                <tr v-for="exercise in exerciseList" :key="exercise.id" class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ exercise.id }}</td>
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ exercise.exercise_type?.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ exercise.lesson?.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">
                                        {{ exercise.prompt && exercise.prompt.length > 20 ? exercise.prompt.slice(0, 20) + '…' : exercise.prompt }}
                                    </td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link v-if="permissions.view" :href="route('exercises.show', exercise.id)">
                                                <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                                    <i class="fa-solid fa-eye mr-2"></i> Ver
                                                </PrimaryButton>
                                            </Link>
                                            <Link v-if="permissions.update" :href="route('exercises.edit', exercise.id)">
                                                <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                                    <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                                                </PrimaryButton>
                                            </Link>
                                            <PrimaryButton v-if="permissions.destroy"
                                                @click="confirm('¿Estás seguro?') && $inertia.delete(route('exercises.destroy', exercise.id))"
                                                class="bg-red-500 hover:bg-red-700 text-white">
                                                <i class="fa-solid fa-trash mr-2"></i> Eliminar
                                            </PrimaryButton>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
