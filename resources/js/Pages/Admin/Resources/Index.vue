<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { computed, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import Form from './Form.vue';

const props = defineProps({
    resources: {
        type: [Object, Array],
        required: true
    },
    units: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ unit: '' })
    }
});
// Filter state
const selectedUnit = ref(props.filters.unit || '');
function applyFilter() {
    router.get(route('resources.index'), { unit: selectedUnit.value }, {
        preserveScroll: true,
    });
}
watch(selectedUnit, (newVal, oldVal) => {
    if (newVal !== oldVal) applyFilter();
});

const resourceList = computed(() => {
    const u = props.resources;
    return Array.isArray(u) ? u : (u?.data ?? []);
});
const links = computed(() => Array.isArray(props.resources) ? [] : (props.resources?.links ?? []));
const meta = computed(() => Array.isArray(props.resources) ? null : ({
    from: props.resources?.from,
    to: props.resources?.to,
    total: props.resources?.total,
}));

const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'name', label: 'Nombre', icon: 'fa-solid fa-font', align: 'left' },
    { key: 'unit.name', label: 'Unidad', icon: 'fa-solid fa-layer-group', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'download', label: 'Archivo', icon: 'fa-solid fa-file', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'description', label: 'Descripción', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
];

function deleteResource(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('resources.destroy', id), {
        preserveScroll: true,
        onSuccess: () => router.reload({ preserveScroll: true }),
    });
}

// Modal state
const showCreate = ref(false);
const showEdit = ref(false);
const editingResource = ref(null);

// Create form
const createForm = useForm({
    name: '',
    description: '',
    unit_id: props.filters?.unit || (props.units?.[0]?.id ?? null),
    file_path: null,
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    createForm.unit_id = selectedUnit.value || (props.units?.[0]?.id ?? null);
    showCreate.value = true;
}

function submitCreate() {
    createForm
        .transform((data) => {
            const payload = { ...data };
            if (!payload.file_path) delete payload.file_path;
            return payload;
        })
        .post(route('resources.store'), {
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
    unit_id: null,
    file_path: null,
});

function openEdit(resource) {
    editingResource.value = resource;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = resource.name ?? '';
    editForm.description = resource.description ?? '';
    editForm.unit_id = resource.unit?.id ?? resource.unit_id ?? null;
    editForm.file_path = null;
    showEdit.value = true;
}

function submitEdit() {
    if (!editingResource.value) return;
    editForm
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            if (!payload.file_path) delete payload.file_path;
            return payload;
        })
        .post(route('resources.update', editingResource.value.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                showEdit.value = false;
                router.reload({ preserveScroll: true });
            },
        });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Recursos" />

        <template #header>
            <PageHeader title="Recursos" subtitle="Gestiona, filtra y organiza tus recursos." icon="fa-solid fa-user"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Recursos' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Recurso
                    </PrimaryButton>
                </template>
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <SelectInput v-model="selectedUnit" class="w-56">
                                <option value="">Todas las unidades</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                            </SelectInput>
                        </div>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <DataTable :items="resourceList" :columns="columns" :empty-text="'No se encontraron recursos.'"
                        show-actions>
                        <!-- Archivo (columna visible en md+) -->
                        <template #cell-download="{ row }">
                            <a :href="route('resources.download', row.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                                <i class="fa-solid fa-download mr-2"></i> Descargar
                            </a>
                        </template>

                        <!-- Descripción truncada -->
                        <template #cell-description="{ value }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ value && value.length > 12 ? value.slice(0, 12) + '…' : value }}
                            </span>
                        </template>

                        <!-- Acciones -->
                        <template #actions="{ row }">
                            <Link :href="route('resources.show', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-eye mr-2"></i> Ver
                            </PrimaryButton>
                            </Link>
                            <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                            </PrimaryButton>
                            <!-- Descargar (solo visible en móvil dentro del dropdown) -->
                            <a :href="route('resources.download', row.id)"
                                class="md:hidden inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                                <i class="fa-solid fa-download mr-2"></i> Descargar
                            </a>
                            <PrimaryButton @click="deleteResource(row.id)"
                                class="bg-red-500 hover:bg-red-700 text-white">
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

        <!-- Create Modal -->
        <Modal :show="showCreate" max-width="2xl" @close="showCreate = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Crear Recurso
                </h2>
                <form @submit.prevent="submitCreate" enctype="multipart/form-data">
                    <Form :form="createForm" :units="props.units" submitText="Crear" />
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEdit" max-width="2xl" @close="showEdit = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Editar Recurso
                </h2>
                <form @submit.prevent="submitEdit" enctype="multipart/form-data">
                    <Form :form="editForm" :units="props.units" submitText="Actualizar" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
