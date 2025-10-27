<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import Form from './Form.vue';
import CardSection from '@/Components/CardSection.vue';

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

// Modal state
const showCreate = ref(false);
const showEdit = ref(false);
const editingLevel = ref(null);

// Create form
const createForm = useForm({
    name: '',
    description: '',
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
}

function submitCreate() {
    createForm.post(route('levels.store'), {
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
});

function openEdit(level) {
    editingLevel.value = level;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = level.name ?? '';
    editForm.description = level.description ?? '';
    showEdit.value = true;
}

function submitEdit() {
    if (!editingLevel.value) return;
    editForm.put(route('levels.update', editingLevel.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false;
            router.reload({ preserveScroll: true });
        },
    });
}

function deleteLevel(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('levels.destroy', id), {
        preserveScroll: true,
        onSuccess: () => router.reload({ preserveScroll: true }),
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
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Nivel
                    </PrimaryButton>
                </template>
            </PageHeader>
        </template>

        <CardSection>
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
                    <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </PrimaryButton>
                    <DangerButton @click="deleteLevel(row.id)">
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
                    Crear Nivel
                </h2>
                <form @submit.prevent="submitCreate">
                    <Form :form="createForm" submitText="Crear" />
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEdit" max-width="2xl" @close="showEdit = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Editar Nivel
                </h2>
                <form @submit.prevent="submitEdit">
                    <Form :form="editForm" submitText="Actualizar" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
