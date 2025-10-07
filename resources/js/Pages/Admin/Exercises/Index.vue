<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    exercises: [Object, Array],
    filters: Object,
    types: Array,
    lessons: Array,
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

const form = useForm({
    type: filters.type || '',
    lesson: filters.lesson || '',
});

function applyFilters() {
    // Usamos router.get directamente para evitar que el estado interno del form retenga props antiguos
    router.get(route('exercises.index'), {
        type: form.type || '',
        lesson: form.lesson || '',
    }, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
    });
}

// Watch reactivo: cada cambio en los selects dispara el filtrado
watch(() => [form.type, form.lesson], ([newType, newLesson], [oldType, oldLesson]) => {
    if (newType === oldType && newLesson === oldLesson) return;
    applyFilters();
});

function deleteExercise(id) {
    if (!confirm('¿Estás seguro de eliminar este ejercicio?')) return;
    router.delete(route('exercises.destroy', id));
}

// Responsive columns for table: show Tipo, Lección y Acciones en móvil
const columns = [
    { key: 'id', label: 'ID', icon: 'fa-solid fa-id-badge', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'hidden md:table-cell' },
    { key: 'exercise_type.name', label: 'Tipo', icon: 'fa-solid fa-tags', align: 'left' },
    { key: 'lesson.name', label: 'Lección', icon: 'fa-solid fa-book', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'prompt', label: 'Prompt', icon: 'fa-solid fa-align-left', align: 'left', thClass: 'hidden md:table-cell', tdClass: 'text-gray-600 dark:text-gray-400 hidden md:table-cell' },
];
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
                    <Link :href="route('exercises.create')">
                    <PrimaryButton>
                        <i class="fa-solid fa-plus mr-2"></i>
                        Agregar Ejercicio
                    </PrimaryButton>
                    </Link>
                </template>
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <SelectInput v-model="form.type" class="w-60">
                                <option value="">Todos los tipos de ejercicio</option>
                                <option v-for="type in types" :value="type.id" :key="type.id">{{ type.name }}</option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.lesson" class="w-56">
                                <option value="">Todas las lecciones</option>
                                <option v-for="lesson in lessons" :value="lesson.id" :key="lesson.id">{{ lesson.name }}
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
                            <Link :href="route('exercises.edit', row.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                            </PrimaryButton>
                            </Link>
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
    </AuthenticatedLayout>
</template>
