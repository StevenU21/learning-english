<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/DataTable.vue';
import ImageCell from '@/Components/ImageCell.vue';
import PageHeader from '@/Components/PageHeader.vue';
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
// Pagination links and meta
const links = computed(() => Array.isArray(props.lessons) ? [] : (props.lessons?.links ?? []));
const meta = computed(() => Array.isArray(props.lessons) ? null : ({
    from: props.lessons?.from,
    to: props.lessons?.to,
    total: props.lessons?.total,
}));

// Responsive: on mobile show only Nombre, Unidad y Acciones
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'image', label: 'Imagen', icon: 'fa-solid fa-image', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'name', label: 'Nombre', icon: 'fa-solid fa-font', align: 'left' },
    { key: 'duration', label: 'Duración (min)', icon: 'fa-solid fa-clock', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
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
            <PageHeader title="Lecciones" subtitle="Gestiona, filtra y organiza tus lecciones." icon="fa-solid fa-book"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Lecciones' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('lessons.create')">
                    <PrimaryButton>
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Lección
                    </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <DataTable :items="lessonList" :columns="columns" :empty-text="'No se encontraron lecciones.'"
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
                    <div class="border-t border-gray-200 dark:border-gray-700">
                        <Pagination :links="links" :meta="meta" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
