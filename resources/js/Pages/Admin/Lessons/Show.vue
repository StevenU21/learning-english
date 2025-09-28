<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object });
const { lesson } = props;
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detalles de Lección" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ lesson.name }}
                </h2>
                <div class="space-x-2 flex">
                    <Link :href="route('lessons.index')">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                    </PrimaryButton>
                    </Link>
                    <Link :href="route('lessons.edit', lesson.id)">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max rounded-xl overflow-hidden border border-gray-600">
                            <tbody>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-font mr-2"></i>Nombre
                                    </th>
                                    <td class="p-4 text-gray-300 font-semibold text-lg">{{ lesson.name }}</td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 align-top">
                                        <i class="fa-solid fa-layer-group mr-2"></i>Unidad
                                    </th>
                                    <td class="p-4">
                                        <span v-if="lesson.unit?.name" class="inline-block bg-gray-600/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                            {{ lesson.unit.name }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 align-top">
                                        <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                    </th>
                                    <td class="p-4 text-gray-300 italic">{{ lesson.description || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
