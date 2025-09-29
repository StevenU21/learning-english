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
                    <div v-for="unit in filteredUnits" :key="unit.id"
                        class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col hover:shadow-md transition">
                        <!-- Imagen -->
                        <div class="relative h-40 w-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <img v-if="unit.image_url || unit.image" :src="unit.image_url || unit.image"
                                :alt="'Imagen de ' + unit.name"
                                class="h-full w-full object-cover group-hover:scale-105 transition duration-300" />
                            <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-image text-3xl"></i>
                            </div>
                            <!-- Progreso badge -->
                            <div
                                class="absolute top-2 right-2 text-xs px-2 py-1 rounded-md bg-black/50 backdrop-blur border border-white/10 text-gray-100 font-medium">
                                {{ unit.progress || 0 }}%
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="flex-1 p-4 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200 line-clamp-2 text-sm">{{
                                    unit.name }}
                                </h3>
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed">
                                {{ unit.description || 'Sin descripción' }}
                            </p>

                            <div class="flex flex-wrap gap-2 mt-auto">
                                <span v-if="unit.level?.name"
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-500/15 text-indigo-400 border border-indigo-500/30 px-2 py-0.5 text-[10px] uppercase tracking-wide font-semibold">
                                    <i class="fa-solid fa-signal"></i> {{ unit.level.name }}
                                </span>
                                <span :class="[
                                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide font-semibold border',
                                    statusConfig(unit.status).bg,
                                    statusConfig(unit.status).border,
                                    statusConfig(unit.status).color
                                ]">
                                    <i class="fa-solid fa-circle"></i> {{ statusConfig(unit.status).text }}
                                </span>
                            </div>

                            <!-- Barra de progreso -->
                            <div class="mt-2">
                                <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500"
                                        :class="progressBarColor(unit.progress)"
                                        :style="{ width: (unit.progress || 0) + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="p-4 pt-0 flex items-center justify-between">
                            <Link :href="route('student.units.start', unit.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fa-solid fa-play mr-2"></i> Ingresar
                            </Link>
                            <Link :href="route('student.units.start', { id: unit.id })"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fa-solid fa-book-open mr-2"></i> Lecciones
                            </Link>
                        </div>
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
