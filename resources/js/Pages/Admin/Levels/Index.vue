<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { computed } from 'vue';

const props = defineProps({
    levels: {
        type: [Object, Array],
        required: true
    }
});

const levelList = computed(() => {
    const u = props.levels;
    return Array.isArray(u) ? u : (u?.data ?? []);
});
// Pagination links and meta for paginated levels
const links = computed(() => Array.isArray(props.levels) ? [] : (props.levels?.links ?? []));
const meta = computed(() => Array.isArray(props.levels) ? null : ({
    from: props.levels?.from,
    to: props.levels?.to,
    total: props.levels?.total,
}));

// Responsive: on mobile show only Nombre + Acciones
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'name', label: 'Nombre', icon: 'fa-solid fa-font', align: 'left' },
    { key: 'description', label: 'Descripción', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
];

function deleteLevel(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('levels.destroy', id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Niveles" />

        <template #header>
            <PageHeader title="Niveles" subtitle="Gestiona, filtra y organiza tus niveles." icon="fa-solid fa-list"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Niveles' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('levels.create')">
                    <PrimaryButton>
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Nivel
                    </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <DataTable :items="levelList" :columns="columns" :empty-text="'No se encontraron niveles.'"
                        show-actions>
                        <!-- Descripción truncada -->
                        <template #cell-description="{ value }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ value && value.length > 60 ? value.slice(0, 60) + '…' : value }}
                            </span>
                        </template>

                        <!-- Acciones -->
                        <template #actions="{ row }">
                            <Link :href="route('levels.show', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-eye mr-2"></i> Ver
                            </PrimaryButton>
                            </Link>
                            <Link :href="route('levels.edit', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                            </PrimaryButton>
                            </Link>
                            <PrimaryButton @click="deleteLevel(row.id)" class="bg-red-500 hover:bg-red-700 text-white">
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
