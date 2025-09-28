<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
    resources: {
        type: [Object, Array],
        required: true
    }
});

const resourceList = computed(() => {
    const u = props.resources;
    return Array.isArray(u) ? u : (u?.data ?? []);
});

function deleteResource(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('resources.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Recursos" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Recursos
                </h2>
                <Link :href="route('resources.create')">
                <PrimaryButton>
                    <i class="fa-solid fa-plus mr-2"></i> Agregar Recurso
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
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-id-badge mr-2"></i>ID</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-font mr-2"></i>Nombre</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-layer-group mr-2"></i>Unidad</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-file mr-2"></i>Archivo</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-align-left mr-2"></i>Descripción</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4"><i
                                            class="fa-solid fa-cogs mr-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="resourceList.length === 0">
                                    <td colspan="6"
                                        class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron recursos.
                                    </td>
                                </tr>
                                <tr v-for="resource in resourceList" :key="resource.id"
                                    class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ resource.id }}</td>
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ resource.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ resource.unit?.name || ''
                                    }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">
                                        <a :href="route('resources.download', resource.id)"
                                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                                            <i class="fa-solid fa-download mr-2"></i> Descargar
                                        </a>
                                    </td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">
                                        {{ resource.description && resource.description.length > 12 ?
                                            resource.description.slice(0, 12) + '…' : resource.description }}
                                    </td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link :href="route('resources.show', resource.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-eye mr-2"></i> Ver</PrimaryButton>
                                            </Link>
                                            <Link :href="route('resources.edit', resource.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-pen-to-square mr-2"></i> Editar</PrimaryButton>
                                            </Link>
                                            <PrimaryButton @click="deleteResource(resource.id)"
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
