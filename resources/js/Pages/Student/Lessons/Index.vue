<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    lessons: { type: Array, required: true },
});

// Ordenar lecciones (opcional: primero en progreso, luego no comenzadas, luego completadas)
const orderedLessons = computed(() => {
    const priority = { en_progreso: 0, no_comenzado: 1, completado: 2 };
    return [...props.lessons].sort((a, b) => {
        const pa = priority[a.status] ?? 99;
        const pb = priority[b.status] ?? 99;
        if (pa !== pb) return pa - pb;
        return a.name.localeCompare(b.name);
    });
});

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

        <Head :title="'Lecciones - ' + unit.name" />

        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <i class="fa-solid fa-book text-gray-400"></i>
                        Lecciones de {{ unit.name }}
                    </h2>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ lessons.length }} lecciones</div>
                </div>
                <Link :href="route('student.units.index')"
                    class="inline-flex w-34 items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="orderedLessons.length === 0"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                        No hay lecciones disponibles.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div v-for="lesson in orderedLessons" :key="lesson.id"
                        class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col hover:shadow-md transition">
                        <div class="flex-1 p-4 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200 line-clamp-2 text-sm">{{
                                    lesson.name
                                    }}</h3>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed">
                                {{ lesson.description || 'Sin descripción' }}
                            </p>

                            <div class="flex flex-wrap gap-2 mt-auto">
                                <span
                                    :class="['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide font-semibold border', statusConfig(lesson.status).bg, statusConfig(lesson.status).border, statusConfig(lesson.status).color]">
                                    <i class="fa-solid fa-circle"></i> {{ statusConfig(lesson.status).text }}
                                </span>
                            </div>

                            <div class="mt-2">
                                <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500"
                                        :class="progressBarColor(lesson.progress)"
                                        :style="{ width: (lesson.progress || 0) + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 pt-0 flex items-center justify-between flex-wrap gap-2">
                            <Link
                                :href="route('student.lessons.start', lesson.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
                            >
                                <i class="fa-solid fa-play mr-2"></i> Ingresar
                            </Link>
                            <Link
                                :href="route('student.lessons.show', lesson.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
                            >
                                <i class="fa-solid fa-book-open mr-2"></i> Contenido
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
