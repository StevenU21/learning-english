<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
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
import SelectInput from '@/Components/SelectInput.vue';
</script>

<template>
    <StudentLayout>

        <Head title="Unidades" />
        <template #header>
            <PageHeader title="Unidades" subtitle="Explora las unidades disponibles." icon="fa-solid fa-layer-group"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Unidades' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #filters>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <SelectInput v-model="levelFilter" @change="applyFilter" class="w-56">
                                <option value="">Todos los niveles</option>
                                <option v-for="level in levels" :key="level.id" :value="level.id">{{ level.name }}
                                </option>
                            </SelectInput>
                        </div>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-10">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div v-if="filteredUnits.length === 0"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                        No hay unidades disponibles.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <UnitCard v-for="unit in filteredUnits" :key="unit.id" :unit="unit" />
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
