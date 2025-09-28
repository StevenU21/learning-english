<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Form from './Form.vue';

const props = defineProps({ unit: Object, levels: Array });
const form = useForm({
    name: props.unit.name || '',
    description: props.unit.description || '',
    expected_time: props.unit.expected_time || '',
    level_id: props.unit.level_id || null,
    image: null,
});

function submit() {
    form.post(route('units.update', props.unit.id), {
        _method: 'put',
    });
}
</script>

<template>

    <Head title="Editar Unidad" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar Unidad</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <Form :form="form" :levels="props.levels" submitText="Actualizar" :onSubmit="submit" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
