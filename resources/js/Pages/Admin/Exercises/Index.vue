<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Modal from '@/Components/Modal.vue';
import ExerciseForm from './ExerciseForm.vue';
import { withQuery } from '@/utils/url';

const props = defineProps({
    exercises: [Object, Array],
    filters: Object,
    types: Array,
    lessons: Array,
    allTypes: { type: Array, default: () => [] },
    allLessons: { type: Array, default: () => [] },
    allUnits: { type: Array, default: () => [] },
});

const exerciseList = computed(() => {
    const list = props.exercises;
    return Array.isArray(list) ? list : (list?.data ?? []);
});
const links = computed(() => Array.isArray(props.exercises) ? [] : (props.exercises?.links ?? []));
const meta = computed(() => Array.isArray(props.exercises) ? null : ({
    from: props.exercises?.from,
    to: props.exercises?.to,
    total: props.exercises?.total,
}));
const filters = props.filters;
const types = props.types;
const lessons = props.lessons;
const allTypes = props.allTypes?.length ? props.allTypes : types;
const allLessons = props.allLessons?.length ? props.allLessons : lessons;
const allUnits = props.allUnits ?? [];

const form = useForm({
    type: filters.type || '',
    unit: filters.unit || '',
    lesson: filters.lesson || '',
});

function applyFilters() {
    // Usamos router.get directamente para evitar que el estado interno del form retenga props antiguos
    router.get(route('exercises.index'), {
        type: form.type || '',
        unit: form.unit || '',
        lesson: form.lesson || '',
    }, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
    });
}

// Watch reactivo: cada cambio en los selects dispara el filtrado
watch(() => [form.type, form.unit, form.lesson], ([newType, newUnit, newLesson], [oldType, oldUnit, oldLesson]) => {
    if (newType === oldType && newUnit === oldUnit && newLesson === oldLesson) return;
    applyFilters();
});

// Lista de lecciones dependiente de unidad (si hay unidad seleccionada)
const filteredLessons = computed(() => {
    if (!form.unit) return allLessons;
    return allLessons.filter(l => String(l.unit_id) === String(form.unit));
});

function deleteExercise(id) {
    if (!confirm('¿Estás seguro de eliminar este ejercicio?')) return;
    router.delete(withQuery(route('exercises.destroy', id)), { preserveScroll: true, onSuccess: () => router.reload({ preserveScroll: true }) });
}

// Responsive columns for table: show Tipo, Lección y Acciones en móvil
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'exercise_type.name', label: 'Tipo', icon: 'fa-solid fa-tags', align: 'left' },
    { key: 'lesson.name', label: 'Lección', icon: 'fa-solid fa-book', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'prompt', label: 'Prompt', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
];

// Modal state
const showCreate = ref(false);
const showEdit = ref(false);
const editingExercise = ref(null);

// Create form for modal
const createForm = useForm({
    prompt: '',
    options: [],
    solution: [],
    explanation: '',
    exercise_type_id: filters.type || '',
    lesson_id: filters.lesson || '',
    file: null,
    file_b: null,
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    createForm.exercise_type_id = filters.type || '';
    createForm.lesson_id = filters.lesson || '';
    showCreate.value = true;
}

function submitCreate() {
    createForm.post(withQuery(route('exercises.store')), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            router.reload({ preserveScroll: true });
        },
    });
}

// Edit form for modal
const editForm = useForm({
    prompt: '',
    options: [],
    solution: [],
    explanation: '',
    exercise_type_id: '',
    lesson_id: '',
    file: null,
    file_b: null,
    file_url: null,
    file_b_url: null,
});

function openEdit(ex) {
    editingExercise.value = ex;
    editForm.reset();
    editForm.clearErrors();
    editForm.prompt = ex.prompt || '';
    editForm.options = Array.isArray(ex.options) ? [...ex.options] : [];
    editForm.solution = Array.isArray(ex.solution) ? [...ex.solution] : [];
    editForm.explanation = ex.explanation || '';
    editForm.exercise_type_id = ex.exercise_type?.id ?? ex.exercise_type_id ?? '';
    editForm.lesson_id = ex.lesson?.id ?? ex.lesson_id ?? '';
    editForm.file = null;
    editForm.file_b = null;
    editForm.file_url = ex.file_url || null;
    editForm.file_b_url = ex.file_b_url || null;
    showEdit.value = true;
}

function submitEdit() {
    if (!editingExercise.value) return;
    editForm
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            delete payload.file_url;
            delete payload.file_b_url;
            if (!payload.file) delete payload.file;
            if (!payload.file_b) delete payload.file_b;
            return payload;
        })
        .post(withQuery(route('exercises.update', editingExercise.value.id)), {
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

        <Head title="Ejercicios" />

        <template #header>
            <PageHeader title="Ejercicios" subtitle="Gestiona, filtra y organiza tus ejercicios."
                icon="fa-solid fa-pencil" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Ejercicios' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <PrimaryButton @click="openCreate">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Ejercicio
                    </PrimaryButton>
                </template>
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <SelectInput v-model="form.unit" class="w-60">
                                <option value="">Todas las unidades</option>
                                <option v-for="unit in allUnits" :value="unit.id" :key="unit.id">{{ unit.name }}
                                </option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.type" class="w-60">
                                <option value="">Todos los tipos de ejercicio</option>
                                <option v-for="type in allTypes" :value="type.id" :key="type.id">{{ type.name }}
                                </option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.lesson" class="w-56">
                                <option value="">Todas las lecciones</option>
                                <option v-for="lesson in filteredLessons" :value="lesson.id" :key="lesson.id">{{
                                    lesson.name
                                    }}
                                </option>
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
                    <DataTable :items="exerciseList" :columns="columns" :empty-text="'No se encontraron ejercicios.'"
                        show-actions>
                        <template #cell-prompt="{ value }">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ value && value.length > 20 ? value.slice(0, 20) + '…' : value }}
                            </span>
                        </template>

                        <!-- Acciones -->
                        <template #actions="{ row }">
                            <Link :href="route('exercises.show', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-eye mr-2"></i> Ver
                            </PrimaryButton>
                            </Link>
                            <PrimaryButton @click="openEdit(row)" class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                            </PrimaryButton>
                            <PrimaryButton @click="deleteExercise(row.id)"
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
        <Modal :show="showCreate" max-width="9xl" @close="showCreate = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Crear Ejercicio
                </h2>
                <form @submit.prevent="submitCreate" enctype="multipart/form-data">
                    <ExerciseForm :form="createForm" :types="allTypes" :lessons="allLessons" :errors="createForm.errors"
                        :on-submit="submitCreate" :is-edit="false" />
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEdit" max-width="9xl" @close="showEdit = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Editar Ejercicio
                </h2>
                <form @submit.prevent="submitEdit" enctype="multipart/form-data">
                    <ExerciseForm :form="editForm" :types="allTypes" :lessons="allLessons" :errors="editForm.errors"
                        :on-submit="submitEdit" :is-edit="true" />
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
