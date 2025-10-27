<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ImageCell from '@/Components/ImageCell.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed, ref } from 'vue';
import PageHeader from '@/Components/PageHeader.vue';
import Modal from '@/Components/Modal.vue';
import Form from './Form.vue';
import Badge from '@/Components/Badge.vue';
import LevelBadge from '@/Components/LevelBadge.vue';
import CardSection from '@/Components/CardSection.vue';

const props = defineProps({
    units: {
        type: [Object, Array],
        required: true
    },
    levels: {
        type: Array,
        required: true,
    },
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

// Modal state
const showCreate = ref(false);
const showEdit = ref(false);
const editingUnit = ref(null);

// Create form
const createForm = useForm({
    name: '',
    description: '',
    level_id: props.levels?.[0]?.id ?? null,
    image: null,
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    createForm.level_id = props.levels?.[0]?.id ?? null;
    showCreate.value = true;
}

function submitCreate() {
    createForm.post(route('units.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            router.reload({ preserveScroll: true });
        },
    });
}

// Edit form
const editForm = useForm({
    name: '',
    description: '',
    level_id: null,
    image: null,
});

function openEdit(unit) {
    editingUnit.value = unit;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = unit.name ?? '';
    editForm.description = unit.description ?? '';
    editForm.level_id = unit.level?.id ?? unit.level_id ?? null;
    editForm.image = null;
    showEdit.value = true;
}

function submitEdit() {
    if (!editingUnit.value) return;
    editForm
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            if (!payload.image) delete payload.image;
            return payload;
        })
        .post(route('units.update', editingUnit.value.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                showEdit.value = false;
                router.reload({ preserveScroll: true });
            },
        });
}

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
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Unidad
                    </PrimaryButton>
                </template>
            </PageHeader>
        </template>

        <CardSection>
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

                <!-- Duración con Badge info -->
                <template #cell-expected_time="{ value }">
                    <Badge type="info">{{ value }}</Badge>
                </template>

                <!-- Nivel con LevelBadge -->
                <template #cell-level.name="{ row }">
                    <LevelBadge :level="row.level.name" />
                </template>

                <!-- Acciones -->
                <template #actions="{ row }">
                    <Link :href="route('units.show', row.id)">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-eye mr-2"></i> Ver
                    </PrimaryButton>
                    </Link>
                    <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </PrimaryButton>
                    <DangerButton @click="deleteUnit(row.id)">
                        <i class="fa-solid fa-trash mr-2"></i> Eliminar
                    </DangerButton>
                </template>
            </DataTable>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <Pagination :links="links" :meta="meta" />
            </div>
        </CardSection>

        <!-- Create Modal -->
        <Modal :show="showCreate" max-width="2xl" @close="showCreate = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Crear Unidad
                </h2>
                <form @submit.prevent="submitCreate" enctype="multipart/form-data">
                    <Form :form="createForm" :levels="props.levels" submitText="Crear" />
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEdit" max-width="2xl" @close="showEdit = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Editar Unidad
                </h2>
                <form @submit.prevent="submitEdit" enctype="multipart/form-data">
                    <Form :form="editForm" :levels="props.levels" submitText="Actualizar" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos de esta página (el header usa estilos encapsulados en PageHeader.vue) */
</style>
