<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Form from './Form.vue';

const props = defineProps({ resource: Object, units: Array });
const form = useForm({
    name: props.resource.name || '',
    description: props.resource.description || '',
    unit_id: props.resource.unit_id || null,
    file_path: null,
});

function submit() {
    form.put(route('resources.update', props.resource.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Editar Recurso" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editar Recurso
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <Form :form="form" :units="props.units" submitText="Actualizar" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
