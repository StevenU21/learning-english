<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ exerciseType: Object });
const { exerciseType } = props;
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detalles de Tipo de Ejercicio" />

        <template #header>
            <PageHeader :title="exerciseType.name" subtitle="Detalles del tipo de ejercicio."
                icon="fa-solid fa-layer-group" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Tipos de Ejercicio', href: route('exercise-types.index') },
                    { label: 'Detalle' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <div class="space-x-2 flex">
                        <Link :href="route('exercise-types.index')">
                        <PrimaryButton>
                            <i class="fa-solid fa-arrow-left mr-2"></i>
                            Volver a la lista
                        </PrimaryButton>
                        </Link>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="overflow-x-auto">
                        <!-- Desktop/tablet layout -->
                        <div class="hidden md:block">
                            <table class="w-full min-w-max rounded-xl overflow-hidden border border-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 w-48 align-top">
                                            <i class="fa-solid fa-font mr-2"></i>Nombre
                                        </th>
                                        <td class="p-4 text-gray-300 font-semibold text-lg">{{ exerciseType.name }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                        </th>
                                        <td class="p-4 text-gray-300 italic">{{ exerciseType.description || '-' }}</td>
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
                                <div class="text-gray-300 font-semibold text-base">{{ exerciseType.name }}</div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                </div>
                                <div class="text-gray-300 italic">{{ exerciseType.description || '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 
