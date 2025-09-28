<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ levels: [Object, Array] });
const { levels } = props;
const levelList = Array.isArray(levels) ? levels : (levels.data ?? []);
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Niveles" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Niveles
                </h2>
                <Link :href="route('levels.create')">
                <PrimaryButton>
                    <i class="fa-solid fa-plus mr-2"></i> Agregar Nivel
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max">
                            <thead class="bg-gray-200 dark:bg-gray-900">
                                <tr>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i class="fa-solid fa-id-badge mr-2"></i>ID</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i class="fa-solid fa-font mr-2"></i>Nombre</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i class="fa-solid fa-align-left mr-2"></i>Descripción</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4"><i class="fa-solid fa-cogs mr-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="levelList.length === 0">
                                    <td colspan="4" class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron niveles.
                                    </td>
                                </tr>
                                <tr v-for="level in levelList" :key="level.id" class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ level.id }}</td>
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ level.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">
                                        {{ level.description && level.description.length > 18 ? level.description.slice(0, 60) + '…' : level.description }}
                                    </td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link :href="route('levels.show', level.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i class="fa-solid fa-eye mr-2"></i> Ver</PrimaryButton>
                                            </Link>
                                            <Link :href="route('levels.edit', level.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i class="fa-solid fa-pen-to-square mr-2"></i> Editar</PrimaryButton>
                                            </Link>
                                            <PrimaryButton @click="confirm('¿Estás seguro?') && $inertia.delete(route('levels.destroy', level.id))" class="bg-red-500 hover:bg-red-700 text-white"><i class="fa-solid fa-trash mr-2"></i> Eliminar</PrimaryButton>
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
