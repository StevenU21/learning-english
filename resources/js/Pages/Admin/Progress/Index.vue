<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/DataTable.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    units: Array,
    users: Array,
    lessons: Array,
    progress: { type: [Object, Array], required: true },
    selectedUnit: [String, Number],
    selectedUser: [String, Number],
    selectedLesson: [String, Number],
    selectedStatus: String,
});

const {
    units,
    users,
    lessons,
    progress: progressProp,
    selectedUnit,
    selectedUser,
    selectedLesson,
    selectedStatus,
} = props;
// Derive array of items and pagination props
const progressList = computed(() => Array.isArray(progressProp) ? progressProp : (progressProp.data ?? []));
const links = computed(() => Array.isArray(progressProp) ? [] : (progressProp.links ?? []));
const meta = computed(() => Array.isArray(progressProp) ? null : ({
    from: progressProp.from,
    to: progressProp.to,
    total: progressProp.total,
}));

const form = useForm({
    unit_id: selectedUnit || '',
    user_id: selectedUser || '',
    lesson_id: selectedLesson || '',
    status: selectedStatus || '',
});

function applyFilters() {
    // Usamos router.get directamente para mantener el mismo patrón que en Exercises
    router.get(route('admin.progress.index'), {
        unit_id: form.unit_id || '',
        user_id: form.user_id || '',
        lesson_id: form.lesson_id || '',
        status: form.status || '',
    }, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
    });
}

// Watch reactivo para disparar filtrado automáticamente
watch(() => [form.unit_id, form.user_id, form.lesson_id, form.status],
    ([nu, nuu, nl, ns], [ou, ouu, ol, os]) => {
        if (nu === ou && nuu === ouu && nl === ol && ns === os) return;
        applyFilters();
    }
);

// Columns for DataTable (similar style to Units)
const columns = [
    { key: 'user.name', label: 'Usuario', icon: 'fa-solid fa-user', align: 'left' },
    { key: 'lesson.unit.name', label: 'Unidad', icon: 'fa-solid fa-layer-group', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'lesson.name', label: 'Lección', icon: 'fa-solid fa-book-open', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'progress', label: 'Progreso', icon: 'fa-solid fa-bars-progress', align: 'left', tdClass: 'text-gray-600 dark:text-gray-400' },
    { key: 'status', label: 'Estado', icon: 'fa-solid fa-flag-checkered', align: 'left' },
];
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Progreso" />

        <template #header>
            <PageHeader title="Progreso" subtitle="Consulta, filtra y gestiona el progreso de los usuarios."
                icon="fa-solid fa-bars-progress" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Progreso' }
                ]" gradient-classes="from-indigo-600 to-cyan-600">
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <SelectInput v-model="form.unit_id" class="w-60">
                                <option value="">Todas las Unidades</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.user_id" class="w-60">
                                <option value="">Todos los Usuarios</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }}
                                </option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.lesson_id" class="w-60">
                                <option value="">Todas las Lecciones</option>
                                <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">{{ lesson.name }}
                                </option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.status" class="w-60">
                                <option value="">Todos los Estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completado">Completado</option>
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
                    <DataTable :items="progressList" :columns="columns"
                        :empty-text="'No se encontraron registros de progreso.'" show-actions>
                        <template #cell-status="{ value }">
                            <span class="capitalize">{{ value }}</span>
                        </template>
                        <template #actions="{ row }">
                            <Link :href="route('admin.progress.show', row.user.id)">
                            <PrimaryButton>
                                <i class="fa-solid fa-eye mr-2"></i>
                                Ver detalle
                            </PrimaryButton>
                            </Link>
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
