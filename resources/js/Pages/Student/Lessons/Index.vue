<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import LessonCard from './components/LessonCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import { computed, ref } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    units: { type: Array, required: true },
    lessons: { type: Array, required: true },
    selectedUnit: { type: [String, Number, null], default: null },
});

// Filtro de unidad
const unitFilter = ref(props.selectedUnit || '');

// Unidad seleccionada (para mostrar nombre en el título/encabezado)
const selectedUnit = computed(() => {
    return props.units.find(u => String(u.id) === String(unitFilter.value)) || null;
});

// Lecciones filtradas según unidad seleccionada
const filteredLessons = computed(() => {
    if (!unitFilter.value) return [];
    return props.lessons;
});

// Ordenar lecciones por estado y nombre
const orderedLessons = computed(() => {
    const priority = { en_progreso: 0, no_comenzado: 1, completado: 2 };
    return [...filteredLessons.value].sort((a, b) => {
        const pa = priority[a.status] ?? 99;
        const pb = priority[b.status] ?? 99;
        if (pa !== pb) return pa - pb;
        return a.name.localeCompare(b.name);
    });
});

// Aplicar filtro mediante Inertia
function applyFilter() {
    // Si hay unidad seleccionada, navegar a la ruta con parámetro
    if (unitFilter.value) {
        router.get(route('student.units.start', unitFilter.value), {}, {
            preserveScroll: true,
            replace: true,
        });
    } else {
        // si se limpia el filtro, ir a índice general de lecciones (si existiera) o quedarse
        router.get(route('student.units.index'), {}, {
            preserveScroll: true,
            replace: true,
        });
    }
}
</script>

<template>
    <StudentLayout>

        <Head :title="selectedUnit ? `Lecciones - ${selectedUnit.name}` : 'Lecciones'" />

        <template #header>
            <PageHeader title="Lecciones" subtitle="Explora las lecciones disponibles." icon="fa-solid fa-book"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Lecciones' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <SelectInput v-model="unitFilter" @change="applyFilter" class="w-56">
                                <option value="">Todas las unidades</option>
                                <option v-for="unit in props.units" :key="unit.id" :value="unit.id">{{ unit.name }}
                                </option>
                            </SelectInput>
                        </div>
                    </div>
                </template>
                <template #actions>
                    <Link :href="route('student.units.index')">
                    <PrimaryButton>
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Volver a Unidades
                    </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-10">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div v-if="!unitFilter"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-info-circle text-3xl text-gray-400"></i>
                        Selecciona una unidad para ver sus lecciones.
                    </p>
                </div>
                <div v-else-if="orderedLessons.length === 0"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                        No hay lecciones disponibles.
                    </p>
                </div>
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <LessonCard v-for="lesson in orderedLessons" :key="lesson.id" :lesson="lesson" />
                </div>
            </div>
        </div>

    </StudentLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
