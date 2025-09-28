<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const { units } = defineProps({
    units: Array,
});
</script>

<template>

    <Head title="Unidades" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Unidades</h2>
                <Link :href="route('units.create')"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                Agregar Unidad
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tiempo Esperado</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nivel</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                <tr v-for="unit in units" :key="unit.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ unit.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ unit.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ unit.expected_time }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ unit.level?.name || '' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <Link :href="route('units.show', unit.id)"
                                            class="text-blue-600 hover:text-blue-900 flex items-center gap-1">
                                        <i class="fa-solid fa-eye"></i>
                                        Ver
                                        </Link>
                                        <Link :href="route('units.edit', unit.id)"
                                            class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Editar
                                        </Link>
                                        <Link method="delete" as="button" :href="route('units.destroy', unit.id)"
                                            class="text-red-600 hover:text-red-900 flex items-center gap-1"
                                            @click.prevent="confirm('¿Estás seguro de eliminar esta unidad?')">
                                        <i class="fa-solid fa-trash"></i>
                                        Eliminar
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="units.length === 0">
                                    <td colspan="5"
                                        class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                        No se encontraron unidades.
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
