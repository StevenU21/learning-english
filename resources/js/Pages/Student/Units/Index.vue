<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    levels: { type: Array, required: true },
    units: { type: Array, required: true },
    selectedLevel: { type: [String, Number, null], default: null }
});

const levelFilter = ref(props.selectedLevel || '');

const filteredUnits = computed(() => {
    if (!levelFilter.value) return props.units;
    return props.units.filter(u => String(u.level_id) === String(levelFilter.value));
});

function applyFilter() {
    router.get(route('student.units.index'), { level_id: levelFilter.value || '' }, {
        preserveScroll: true,
        replace: true,
    });
}

function statusConfig(status) {
    switch (status) {
        case 'completado':
            return { text: 'Completado', bg: 'bg-green-600/20', border: 'border-green-600/40', color: 'text-green-400' };
        case 'en_progreso':
            return { text: 'En progreso', bg: 'bg-yellow-500/20', border: 'border-yellow-500/40', color: 'text-yellow-400' };
        default:
            return { text: 'No comenzado', bg: 'bg-gray-500/20', border: 'border-gray-500/40', color: 'text-gray-400' };
    }
}

function progressBarColor(progress) {
    if (progress >= 100) return 'bg-green-500';
    if (progress >= 60) return 'bg-indigo-500';
    if (progress > 0) return 'bg-yellow-500';
    return 'bg-gray-600';
}
import UnitCard from './components/UnitCard.vue';
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Unidades" />
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-gray-400"></i>
                    Unidades
                </h2>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="flex-1 md:flex-initial">
                        <select v-model="levelFilter" @change="applyFilter"
                            class="w-full md:w-56 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos los niveles</option>
                            <option v-for="level in levels" :key="level.id" :value="level.id">{{ level.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="filteredUnits.length === 0"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                        No hay unidades disponibles.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <UnitCard v-for="unit in filteredUnits" :key="unit.id" :unit="unit" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
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
