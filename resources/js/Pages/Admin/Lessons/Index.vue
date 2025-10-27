<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/DataTable.vue';
import ImageCell from '@/Components/ImageCell.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { computed, ref, watch } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import Modal from '@/Components/Modal.vue';
import Form from './Form.vue';
import Badge from '@/Components/Badge.vue';
import CardSection from '@/Components/CardSection.vue';

const props = defineProps({
    lessons: {
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
// Formulario para filtros
// Estado del filtro
const selectedUnit = ref(props.filters.unit || '');
function applyFilter() {
    router.get(route('lessons.index'), { unit: selectedUnit.value }, {
        preserveScroll: true,
    });
}
// Reactivo: aplicar filtro cuando cambie el select
watch(selectedUnit, (newVal, oldVal) => {
    if (newVal !== oldVal) applyFilter();
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
        onSuccess: () => router.reload({ preserveScroll: true }),
    });
}

// Modal state
const showCreate = ref(false);
const showEdit = ref(false);
const editingLesson = ref(null);
const editPreviewUrl = ref(null);

// Create form
const createForm = useForm({
    name: '',
    description: '',
    duration: '',
    image: null,
    unit_id: props.filters?.unit || (props.units?.[0]?.id ?? null),
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    // Prefill unit with current filter if any
    createForm.unit_id = selectedUnit.value || (props.units?.[0]?.id ?? null);
    showCreate.value = true;
}

function submitCreate() {
    createForm.post(route('lessons.store'), {
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
    duration: '',
    unit_id: null,
    image: null,
});

function openEdit(lesson) {
    editingLesson.value = lesson;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = lesson.name ?? '';
    editForm.description = lesson.description ?? '';
    editForm.duration = lesson.duration ?? '';
    editForm.unit_id = lesson.unit?.id ?? lesson.unit_id ?? null;
    editForm.image = null;
    editPreviewUrl.value = lesson.image_url || lesson.image || null;
    showEdit.value = true;
}

function submitEdit() {
    if (!editingLesson.value) return;
    editForm
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            if (!payload.image) delete payload.image;
            return payload;
        })
        .post(route('lessons.update', editingLesson.value.id), {
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

        <Head title="Lecciones" />

        <template #header>
            <PageHeader title="Lecciones" subtitle="Gestiona, filtra y organiza tus lecciones." icon="fa-solid fa-book"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Lecciones' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Lección
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

        <CardSection>
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

                <!-- Duración con Badge -->
                <template #cell-duration="{ value }">
                    <Badge type="info">{{ value }}</Badge>
                </template>

                <!-- Acciones -->
                <template #actions="{ row }">
                    <Link :href="route('lessons.show', row.id)">
                    <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-eye mr-2"></i> Ver
                    </PrimaryButton>
                    </Link>
                    <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                    </PrimaryButton>
                    <DangerButton @click="deleteLesson(row.id)">
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
                    Crear Lección
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
                    Editar Lección
                </h2>
                <form @submit.prevent="submitEdit" enctype="multipart/form-data">
                    <Form :form="editForm" :units="props.units" :preview-url="editPreviewUrl" submitText="Actualizar" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
