<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ExerciseForm from './ExerciseForm.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
    form
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            // Do not send preview-only URL fields
            delete payload.file_url;
            delete payload.file_b_url;
            // Avoid sending null file fields
            if (!payload.file) delete payload.file;
            if (!payload.file_b) delete payload.file_b;
            return payload;
        })
        .post(route('exercises.update', props.exercise.id), {
            forceFormData: true,
        });
}
</script>
<template>
    <AuthenticatedLayout>

        <Head title="Editar Ejercicio" />
        <template #header>
            <PageHeader
                title="Ejercicios"
                subtitle="Edita el ejercicio seleccionado."
                icon="fa-solid fa-pencil"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Ejercicios', href: route('exercises.index') },
                    { label: 'Editar' }
                ]"
                gradient-classes="from-purple-600 to-indigo-600"
            >
                <template #actions>
                    <Link :href="route('exercises.index')">
                        <PrimaryButton>
                            <i class="fa-solid fa-arrow-left mr-2"></i>
                            Volver a la lista
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>
        <div class="py-0">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <ExerciseForm :form="form" :types="props.types" :lessons="props.lessons" :errors="form.errors"
                            :on-submit="submit" :is-edit="true" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
