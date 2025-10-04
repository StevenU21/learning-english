<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ level: Object });
const { level } = props;
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detalles de Nivel" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ level.name }}
                </h2>
                <div class="space-x-2 flex">
                    <Link :href="route('levels.index')">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                    </PrimaryButton>
                    </Link>
                    <Link :href="route('levels.edit', level.id)">
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
                        <!-- Desktop/tablet layout -->
                        <div class="hidden md:block">
                            <table class="w-full min-w-max rounded-xl overflow-hidden border border-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 w-48 align-top">
                                            <i class="fa-solid fa-font mr-2"></i>Nombre
                                        </th>
                                        <td class="p-4 text-gray-300 font-semibold text-lg">{{ level.name }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                        </th>
                                        <td class="p-4 text-gray-300 italic">{{ level.description || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile layout -->
                        <div class="md:hidden p-4 space-y-4">
                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-font mr-2"></i>Nombre
                                </div>
                                <div class="text-gray-300 font-semibold text-base">{{ level.name }}</div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                </div>
                                <div class="text-gray-300 italic">{{ level.description || '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
