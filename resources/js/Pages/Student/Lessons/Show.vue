<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    lesson: { type: Object, required: true },
    exercises: { type: Array, required: true },
});
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`Resumen de ${lesson.name}`" />

        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <i class="fa-solid fa-list text-gray-400"></i>
                        Resumen de {{ lesson.name }}
                    </h2>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ exercises.length }} ejercicios</div>
                </div>
                <Link :href="route('student.units.start', lesson.unit_id)"
                    class="inline-flex w-34 items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="exercises.length" class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="ex in exercises" :key="ex.id"
                        class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ ex.exercise_type.name }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ ex.prompt }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-center text-gray-600 dark:text-gray-400">
                    No hay ejercicios en esta lección.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
