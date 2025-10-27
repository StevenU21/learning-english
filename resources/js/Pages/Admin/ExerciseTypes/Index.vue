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
    exerciseTypes: {
        type: [Object, Array],
        required: true
    }
});

const exerciseTypeList = computed(() => {
    const u = props.exerciseTypes;
    return Array.isArray(u) ? u : (u?.data ?? []);
});
// Pagination links and meta for paginated exercise types
const links = computed(() => Array.isArray(props.exerciseTypes) ? [] : (props.exerciseTypes?.links ?? []));
const meta = computed(() => Array.isArray(props.exerciseTypes) ? null : ({
    from: props.exerciseTypes?.from,
    to: props.exerciseTypes?.to,
    total: props.exerciseTypes?.total,
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
const editingExerciseType = ref(null);

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
    createForm.post(route('exercise-types.store'), {
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

function openEdit(exerciseType) {
    editingExerciseType.value = exerciseType;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = exerciseType.name ?? '';
    editForm.description = exerciseType.description ?? '';
    showEdit.value = true;
}

function submitEdit() {
    if (!editingExerciseType.value) return;
    editForm.put(route('exercise-types.update', editingExerciseType.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false;
            router.reload({ preserveScroll: true });
        },
    });
}

function deleteExerciseType(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('exercise-types.destroy', id), {
        preserveScroll: true,
        onSuccess: () => router.reload({ preserveScroll: true }),
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Tipos de Ejercicio" />

        <template #header>
            <PageHeader title="Tipos de Ejercicio" subtitle="Gestiona, filtra y organiza tus tipos de ejercicio."
                icon="fa-solid fa-list" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Tipos de Ejercicio' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Tipo de Ejercicio
                    </PrimaryButton>
                </template>
            </PageHeader>
        </template>

        <CardSection>
            <DataTable :items="exerciseTypeList" :columns="columns"
                :empty-text="'No se encontraron tipos de ejercicio.'" show-actions>
                <!-- Descripción truncada -->
                <template #cell-description="{ value }">
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ value && value.length > 60 ? value.slice(0, 60) + '…' : value }}
                    </span>
                </template>

                <!-- Acciones -->
                <template #actions="{ row }">
                    <Link :href="route('exercise-types.show', row.id)">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-eye mr-2"></i> Ver
                    </PrimaryButton>
                    </Link>
                    <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </PrimaryButton>
                    <DangerButton @click="deleteExerciseType(row.id)">
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
                    Crear Tipo de Ejercicio
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
                    Editar Tipo de Ejercicio
                </h2>
                <form @submit.prevent="submitEdit">
                    <Form :form="editForm" submitText="Actualizar" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
