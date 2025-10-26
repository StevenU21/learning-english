<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import Badge from '@/Components/Badge.vue';
import { computed } from 'vue';

const props = defineProps({
    lesson: { type: Object, required: true },
    exercises: { type: Array, required: true },
});

// Estadísticas de tipos de ejercicio
const exerciseTypeSummary = computed(() => {
    const summary = {};
    props.exercises.forEach(ex => {
        const type = ex.exercise_type?.name || 'Otro';
        summary[type] = (summary[type] || 0) + 1;
    });
    return summary;
});
</script>

<template>
    <StudentLayout>

        <Head :title="`Resumen de ${lesson.name}`" />
        <div class="py-10">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Estadísticas rápidas y resumen -->
                <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                        <div class="mb-2">
                            <i class="fas fa-clock h-7 w-7 text-blue-500 dark:text-blue-400"></i>
                        </div>
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Duración
                        </div>
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ lesson.duration }} min</div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                        <div class="mb-2">
                            <i class="fas fa-signal h-7 w-7 text-green-500 dark:text-green-400"></i>
                        </div>
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Dificultad
                        </div>
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ lesson.difficulty ?? 'N/A'
                            }}</div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700 flex flex-col items-center">
                        <div class="mb-2">
                            <i class="fas fa-tasks h-7 w-7 text-purple-500 dark:text-purple-400"></i>
                        </div>
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Ejercicios
                        </div>
                        <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ exercises.length }}</div>
                    </div>
                </div>
                <!-- Fin estadísticas rápidas -->
                <div v-if="exercises.length" class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="ex in exercises" :key="ex.id"
                        class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ ex.exercise_type.name }}
                            </h3>
                            <Badge type="info">Intento: {{ ex.attempt_number ?? 1 }}</Badge>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ ex.prompt }}
                        </p>
                    </div>
                </div>
                <div v-else
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    No hay ejercicios en esta lección.
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
