<template>
    <div
        class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col hover:shadow-md transition">
        <!-- Imagen -->
        <div class="relative h-40 w-full overflow-hidden bg-gray-200 dark:bg-gray-700">
            <template v-if="lesson.image_url || lesson.image">
                <img :src="lesson.image_url || lesson.image" :alt="`Imagen de ${lesson.name}`"
                    class="h-full w-full object-cover group-hover:scale-105 transition duration-300" />
            </template>
            <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                <i class="fa-solid fa-image text-3xl"></i>
            </div>
            <!-- Progreso badge -->
            <div
                class="absolute top-2 right-2 text-xs px-2 py-1 rounded-md bg-black/50 backdrop-blur border border-white/10 text-gray-100 font-medium">
                {{ lesson.progress || 0 }}%
            </div>
        </div>

        <!-- Contenido -->
        <div class="flex-1 p-4 flex flex-col gap-3">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 line-clamp-2 text-sm">{{ lesson.name }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed">
                {{ lesson.description || 'Sin descripción' }}
            </p>
            <div class="flex items-center gap-2">
                <span v-if="lesson.duration"
                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                    <i class="fa-solid fa-clock"></i> {{ lesson.duration }} min
                </span>
                <span :class="[
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide font-semibold border',
                    statusConfig(lesson.status).bg,
                    statusConfig(lesson.status).border,
                    statusConfig(lesson.status).color
                ]">
                    <i class="fa-solid fa-circle"></i> {{ statusConfig(lesson.status).text }}
                </span>
            </div>
            <!-- Barra de progreso -->
            <div class="mt-2">
                <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-500" :class="progressBarColor(lesson.progress)"
                        :style="{ width: (lesson.progress || 0) + '%' }"></div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="p-4 pt-0 flex items-center justify-between flex-wrap gap-2">
            <Link
                :href="route('student.units.lessons.sequence', { unit: lesson.unit?.slug || lesson.unit_slug || lesson.unit_id, lesson: lesson.slug || lesson.id })"
                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
            <i class="fa-solid fa-play mr-2"></i> Ingresar
            </Link>
            <Link
                :href="route('student.units.lessons.show', { unit: lesson.unit?.slug || lesson.unit_slug || lesson.unit_id, lesson: lesson.slug || lesson.id })"
                class="inline-flex items-center rounded-md border-2 border-gray-200 dark:border-gray-700 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800">
                <i class="fa-solid fa-book-open mr-2"></i> Contenido
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
const props = defineProps({ lesson: Object });

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
