<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Form from './Form.vue';

const props = defineProps({ units: Array });
const form = useForm({
    name: '',
    description: '',
    unit_id: props.units.length ? props.units[0].id : null,
    file_path: null,
});

function submit() {
    form.post(route('resources.store'));
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Crear Recurso" />
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Crear Recurso
                </h2>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <Form :form="form" :units="props.units" submitText="Crear" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
