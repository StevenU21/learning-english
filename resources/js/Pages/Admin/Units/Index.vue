<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageCell from '@/Components/ImageCell.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';

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
const links = computed(() => Array.isArray(props.units) ? [] : props.units?.links ?? []);
const meta = computed(() => Array.isArray(props.units) ? null : ({
    from: props.units.from,
    to: props.units.to,
    total: props.units.total,
}));

// Responsive: hide all but "Nivel" and "Acciones" on mobile using hidden md:table-cell
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'image', label: 'Imagen', icon: 'fa-solid fa-image', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    // Nombre debe mostrarse también en móvil (sin clases hidden)
    { key: 'name', label: 'Nombre', icon: 'fa-solid fa-font', align: 'left' },
    { key: 'description', label: 'Descripción', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
    { key: 'expected_time', label: 'Duración', icon: 'fa-solid fa-clock', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
    { key: 'level.name', label: 'Nivel', icon: 'fa-solid fa-layer-group', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
];

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
            <PageHeader title="Unidades" subtitle="Gestiona, filtra y organiza tus unidades."
                icon="fa-solid fa-layer-group" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Unidades' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('units.create')">
                    <PrimaryButton>
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Unidad
                    </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <DataTable :items="unitList" :columns="columns" :empty-text="'No se encontraron unidades.'"
                        show-actions>
                        <!-- Imagen -->
                        <template #cell-image="{ row }">
                            <div class="px-0 py-0">
                                <ImageCell :src="row.image_url || row.image" alt="Imagen de la unidad" />
                            </div>
                        </template>

                        <!-- Descripción truncada -->
                        <template #cell-description="{ value }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ value && value.length > 12 ? value.slice(0, 12) + '…' : value }}
                            </span>
                        </template>

                        <!-- Acciones -->
                        <template #actions="{ row }">
                            <Link :href="route('units.show', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-eye mr-2"></i> Ver
                            </PrimaryButton>
                            </Link>
                            <Link :href="route('units.edit', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                            </PrimaryButton>
                            </Link>
                            <PrimaryButton @click="deleteUnit(row.id)" class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-trash mr-2"></i> Eliminar
                            </PrimaryButton>
                        </template>
                    </DataTable>
                    <div class="border-t border-gray-200 dark:border-gray-700">
                        <Pagination :links="links" :meta="meta" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos de esta página (el header usa estilos encapsulados en PageHeader.vue) */
</style>
