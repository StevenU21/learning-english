<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ExerciseForm from './ExerciseForm.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    types: { type: Array, required: true },
    lessons: { type: Array, required: true }
});

const form = useForm({
    prompt: props.exercise.prompt || '',
    options: props.exercise.options || [],
    solution: props.exercise.solution || [],
    explanation: props.exercise.explanation || '',
    exercise_type_id: props.exercise.exercise_type_id || '',
    lesson_id: props.exercise.lesson_id || '',
    file: null,
    file_b: null,
    file_url: props.exercise.file_url || null,
    file_b_url: props.exercise.file_b_url || null
});

function submit() {
    form.put(route('exercises.update', props.exercise.id), { forceFormData: true });
}
</script>
<template>
    <AuthenticatedLayout>

        <Head title="Editar Ejercicio" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Editar Ejercicio</h2>
        </template>
        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <ExerciseForm :form="form" :types="props.types" :lessons="props.lessons" :errors="form.errors"
                            :on-submit="submit" :is-edit="true" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
