<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageCell from '@/Components/ImageCell.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ unit: Object });
const { unit } = props;

const formatDate = (date) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString(undefined, options);
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detalles de Unidad" />

        <template #header>
            <PageHeader
                :title="unit.name"
                subtitle="Detalles de la unidad."
                icon="fa-solid fa-layer-group"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Unidades', href: route('units.index') },
                    { label: 'Detalle' }
                ]"
                gradient-classes="from-purple-600 to-indigo-600"
            >
                <template #actions>
                    <div class="space-x-2 flex">
                        <Link :href="route('units.index')">
                            <PrimaryButton>
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Volver a la lista
                            </PrimaryButton>
                        </Link>
                        <Link :href="route('units.edit', unit.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                Editar
                            </PrimaryButton>
                        </Link>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="overflow-x-auto">
                        <!-- Desktop/tablet layout -->
                        <div class="hidden md:block">
                            <table class="w-full min-w-max rounded-xl overflow-hidden border border-gray-600">
                                <tbody>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 w-48 align-top">
                                            <i class="fa-solid fa-font mr-2"></i>Nombre
                                        </th>
                                        <td class="p-4 text-gray-300 font-semibold text-lg">{{ unit.name }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                        </th>
                                        <td class="p-4 text-gray-300 italic">{{ unit.description || '-' }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-clock mr-2"></i>Duración
                                        </th>
                                        <td class="p-4">
                                            <span
                                                class="inline-block bg-gray-700/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                                {{ unit.expected_time }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-layer-group mr-2"></i>Nivel
                                        </th>
                                        <td class="p-4">
                                            <span v-if="unit.level?.name"
                                                class="inline-block bg-gray-600/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                                {{ unit.level.name }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-image mr-2"></i>Imagen
                                        </th>
                                        <td class="p-4">
                                            <div class="flex items-center justify-center">
                                                <div
                                                    class="bg-purple-900/60 rounded-2xl shadow-2xl border-4 border-purple-500 p-1 flex items-center justify-center">
                                                    <ImageCell :src="unit.image_url || unit.image"
                                                        alt="Imagen de la unidad" :width="300" :height="300"
                                                        class="rounded-xl shadow-xl transition-transform duration-200 hover:scale-105 hover:shadow-2xl"
                                                        style="width: 100%; height: 100%; object-fit: contain; background: #222; display: block;" />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-600/40 transition">
                                        <th class="text-left p-4 text-gray-200 align-top">
                                            <i class="fa-solid fa-calendar-alt mr-2"></i>Creado
                                        </th>
                                        <td class="p-4 text-gray-400">{{ formatDate(unit.created_at) }}</td>
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
                                <div class="text-gray-300 font-semibold text-base">{{ unit.name }}</div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-align-left mr-2"></i>Descripción
                                </div>
                                <div class="text-gray-300 italic">{{ unit.description || '-' }}</div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-clock mr-2"></i>Duración
                                </div>
                                <div>
                                    <span
                                        class="inline-block bg-gray-700/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                        {{ unit.expected_time }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-layer-group mr-2"></i>Nivel
                                </div>
                                <div>
                                    <span v-if="unit.level?.name"
                                        class="inline-block bg-gray-600/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                        {{ unit.level.name }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-2">
                                    <i class="fa-solid fa-image mr-2"></i>Imagen
                                </div>
                                <div class="flex items-center justify-center">
                                    <div class="bg-purple-900/60 rounded-2xl shadow-2xl border-4 border-purple-500 p-1">
                                        <ImageCell :src="unit.image_url || unit.image" alt="Imagen de la unidad"
                                            :width="260" :height="260" class="rounded-xl shadow-xl"
                                            style="width: 100%; height: auto; object-fit: contain; background: #222; display: block;" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                                <div class="text-gray-200 text-sm font-semibold mb-1">
                                    <i class="fa-solid fa-calendar-alt mr-2"></i>Creado
                                </div>
                                <div class="text-gray-400">{{ formatDate(unit.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
