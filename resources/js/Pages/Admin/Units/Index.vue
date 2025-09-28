<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageCell from '@/Components/ImageCell.vue';
import { computed } from 'vue';

const props = defineProps({
    units: {
        type: [Object, Array],
        required: true
    }
});

const unitList = computed(() => {
    const u = props.units;
    return Array.isArray(u) ? u : (u?.data ?? []);
});

function deleteUnit(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('units.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            // Recarga los props para actualizar la lista
            router.reload({ preserveScroll: true });
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Unidades" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Unidades
                </h2>
                <Link :href="route('units.create')">
                <PrimaryButton>
                    <i class="fa-solid fa-plus mr-2"></i> Agregar Unidad
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
                                            class="fa-solid fa-image mr-2"></i>Imagen</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-font mr-2"></i>Nombre</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-align-left mr-2"></i>Descripción</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-clock mr-2"></i>Duración</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-layer-group mr-2"></i>Nivel</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4"><i
                                            class="fa-solid fa-cogs mr-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="unitList.length === 0">
                                    <td colspan="7"
                                        class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron unidades.
                                    </td>
                                </tr>
                                <tr v-for="unit in unitList" :key="unit.id"
                                    class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ unit.id }}</td>
                                    <td class="px-4 py-2">
                                        <ImageCell :src="unit.image_url || unit.image" alt="Imagen de la unidad" />
                                    </td>
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ unit.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ unit.description &&
                                        unit.description.length > 12 ? unit.description.slice(0, 12) + '…' :
                                        unit.description }}
                                    </td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ unit.expected_time }}
                                    </td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ unit.level?.name || ''
                                        }}
                                    </td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link :href="route('units.show', unit.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-eye mr-2"></i> Ver</PrimaryButton>
                                            </Link>
                                            <Link :href="route('units.edit', unit.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-pen-to-square mr-2"></i> Editar
                                            </PrimaryButton>
                                            </Link>
                                            <PrimaryButton @click="deleteUnit(unit.id)"
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
