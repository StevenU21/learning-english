<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Form from './Form.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';


const props = defineProps({ exerciseType: Object });
const form = useForm({
    name: props.exerciseType.name || '',
    description: props.exerciseType.description || '',
});

function submit() {
    form.put(route('exercise-types.update', props.exerciseType.id));
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Editar Nivel" />
        <template #header>
            <PageHeader title="Niveles" subtitle="Edita el nivel seleccionado." icon="fa-solid fa-layer-group"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Tipos de Ejercicio', href: route('exercise-types.index') },
                    { label: 'Editar' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('exercise-types.index')">
                    <PrimaryButton>
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Volver a la lista
                    </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>
        </template>
        <div class="py-0">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-8">
                    <form @submit.prevent="submit">
                        <Form :form="form" submitText="Actualizar" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
