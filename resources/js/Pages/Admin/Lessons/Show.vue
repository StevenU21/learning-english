<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageCell from '@/Components/ImageCell.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ lesson: Object });
const { lesson } = props;
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Detalles de Lección" />

        <template #header>
            <PageHeader :title="lesson.name" subtitle="Detalles de la lección." icon="fa-solid fa-book-open"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Lecciones', href: route('lessons.index') },
                    { label: 'Detalle' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <div class="space-x-2 flex">
                        <Link :href="route('lessons.index')">
                        <PrimaryButton>
                            <i class="fa-solid fa-arrow-left mr-2"></i>
                            Volver a la lista
                        </PrimaryButton>
                        </Link>
                        <Link :href="route('lessons.edit', lesson.id)">
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
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
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
                                        <i class="fa-solid fa-clock mr-2"></i>Duración (min)
                                    </th>
                                    <td class="p-4 text-gray-300">{{ lesson.duration ?? '-' }}</td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 align-top">
                                        <i class="fa-solid fa-layer-group mr-2"></i>Unidad
                                    </th>
                                    <td class="p-4">
                                        <span v-if="lesson.unit?.name"
                                            class="inline-block bg-gray-600/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
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
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 align-top">
                                        <i class="fa-solid fa-image mr-2"></i>Imagen
                                    </th>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center">
                                            <div
                                                class="bg-purple-900/60 rounded-2xl shadow-2xl border-4 border-purple-500 p-1">
                                                <ImageCell :src="lesson.image_url || lesson.image"
                                                    alt="Imagen de la lección" :width="300" :height="300"
                                                    class="rounded-xl shadow-xl"
                                                    style="width: 100%; height: auto; object-fit: contain; background: #222; display: block;" />
                                            </div>
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
