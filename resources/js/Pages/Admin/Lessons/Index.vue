<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import { computed } from 'vue';

const props = defineProps({
    lessons: {
        type: [Object, Array],
        required: true
    }
});

const lessonList = computed(() => {
    const u = props.lessons;
    return Array.isArray(u) ? u : (u?.data ?? []);
});

// Responsive: on mobile show only Nombre, Unidad y Acciones
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'name', label: 'Nombre', icon: 'fa-solid fa-font', align: 'left' },
    { key: 'unit.name', label: 'Unidad', icon: 'fa-solid fa-layer-group', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'description', label: 'Descripción', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
];

function deleteLesson(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('lessons.destroy', id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Lecciones" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Lecciones
                </h2>
                <Link :href="route('lessons.create')">
                <PrimaryButton>
                    <i class="fa-solid fa-plus mr-2"></i> Agregar Lección
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <DataTable
                        :items="lessonList"
                        :columns="columns"
                        :empty-text="'No se encontraron lecciones.'"
                        show-actions
                    >
                        <!-- Descripción truncada -->
                        <template #cell-description="{ value }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ value && value.length > 12 ? value.slice(0, 12) + '…' : value }}
                            </span>
                        </template>

                        <!-- Acciones -->
                        <template #actions="{ row }">
                            <Link :href="route('lessons.show', row.id)">
                                <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                    <i class="fa-solid fa-eye mr-2"></i> Ver
                                </PrimaryButton>
                            </Link>
                            <Link :href="route('lessons.edit', row.id)">
                                <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                    <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                                </PrimaryButton>
                            </Link>
                            <PrimaryButton @click="deleteLesson(row.id)" class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-trash mr-2"></i> Eliminar
                            </PrimaryButton>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
