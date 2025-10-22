<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import StatusBadge from '@/Components/StatusBadge.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
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

const progressList = computed(() => {
    const list = props.progress;
    return Array.isArray(list) ? list : (list?.data ?? []);
});
const links = computed(() => Array.isArray(props.progress) ? [] : (props.progress?.links ?? []));
const meta = computed(() => Array.isArray(props.progress) ? null : ({
    from: props.progress?.from,
    to: props.progress?.to,
    total: props.progress?.total,
}));

const form = useForm({
    unit_id: props.selectedUnit || '',
    user_id: props.selectedUser || '',
    lesson_id: props.selectedLesson || '',
    status: props.selectedStatus || '',
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
        preserveScroll: true
    });
}

// Watch reactivo para disparar filtrado automáticamente
watch(() => [form.unit_id, form.user_id, form.lesson_id, form.status],
    ([nu, nuu, nl, ns], [ou, ouu, ol, os]) => {
        // Si se cambia la unidad y la lección ya no pertenece a esa unidad, limpiar lesson_id
        if (nu && nl) {
            const match = lessons.find(l => String(l.id) === String(nl));
            if (match && String(match.unit_id) !== String(nu)) {
                form.lesson_id = '';
            }
        }
        if (nu === ou && nuu === ouu && nl === ol && ns === os) return;
        applyFilters();
    }
);

// Lista de lecciones dependiente de unidad
const filteredLessons = computed(() => {
    if (!form.unit_id) return props.lessons;
    return props.lessons.filter(l => String(l.unit_id) === String(form.unit_id));
});

// Columns for DataTable (similar style to Units)
const columns = [
    {
        key: 'avatar_url', label: 'Imagen', icon: 'fa-solid fa-image', align: 'left', thClass: 'w-18', tdClass: 'text-gray-600 dark:text-gray-400',
    },
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
                                <option v-for="lesson in filteredLessons" :key="lesson.id" :value="lesson.id">{{
                                    lesson.name }}</option>
                            </SelectInput>
                        </div>
                        <div>
                            <SelectInput v-model="form.status" class="w-60">
                                <option value="">Todos los Estados</option>
                                <option value="no_comenzado">Pendiente</option>
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
                        :empty-text="'No se encontraron registros de progreso.'" show-actions :links="links"
                        :meta="meta">
                        <template #cell-status="{ value }">
                            <StatusBadge :status="value" />
                        </template>
                        <template #cell-progress="{ value }">
                            <ProgressBar :value="value" />
                        </template>
                        <template #actions="{ row }">
                            <Link :href="route('admin.progress.show', row.user.id)">
                            <PrimaryButton>
                                <i class="fa-solid fa-eye mr-2"></i>
                                Ver detalle
                            </PrimaryButton>
                            </Link>
                        </template>
                        <template #cell-avatar_url="{ row }">
                            <img :src="row.avatar_url || '/img/logo03.png'" alt="Avatar"
                                class="w-12 h-12 rounded-full object-cover" />
                        </template>
                    </DataTable>
                    <!-- La paginación ahora la maneja DataTable -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
