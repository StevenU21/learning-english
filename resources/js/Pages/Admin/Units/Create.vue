<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Form from './Form.vue';

const props = defineProps({ levels: Array });
const form = useForm({
    name: '',
    description: '',
    expected_time: '',
    level_id: props.levels.length ? props.levels[0].id : null,
    image: null,
});

function submit() {
    form.post(route('units.store'));
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Crear Unidad" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Crear Unidad
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <Form :form="form" :levels="props.levels" submitText="Crear" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
