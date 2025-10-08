<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    lesson: { type: Object, required: true },
    exercises: { type: Array, required: true },
});
</script>

<template>
    <StudentLayout>

        <Head :title="`Resumen de ${lesson.name}`" />

        <template #header>
            <PageHeader :title="`Resumen de ${lesson.name}`" :subtitle="`${exercises.length} ejercicios`"
                icon="fa-solid fa-list" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Unidades', href: route('student.units.index') },
                    { label: 'Lección' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('student.units.lessons.index', { unit: lesson.unit?.slug || lesson.unit_slug || lesson.unit_id })"
                        class="inline-flex w-34 items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-10">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div v-if="exercises.length" class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="ex in exercises" :key="ex.id"
                        class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ ex.exercise_type.name }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ ex.prompt }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-400/40 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    No hay ejercicios en esta lección.
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
