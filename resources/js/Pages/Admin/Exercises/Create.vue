<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ExerciseForm from './ExerciseForm.vue';

const props = defineProps({
  types: { type: Array, required: true },
  lessons: { type: Array, required: true }
});

const form = useForm({
  prompt: '',
  options: [],
  solution: [],
  explanation: '',
  exercise_type_id: '',
  lesson_id: '',
  file: null,
  file_b: null
});

function submit() {
  form.post(route('exercises.store'));
}
</script>
<template>
  <AuthenticatedLayout>
    <Head title="Crear Ejercicio" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Crear Ejercicio</h2>
    </template>
    <div class="py-8">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
          <ExerciseForm :form="form" :types="props.types" :lessons="props.lessons" :errors="form.errors" :on-submit="submit" :is-edit="false" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
