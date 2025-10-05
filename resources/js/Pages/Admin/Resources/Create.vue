<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Form from './Form.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ units: Array });
const form = useForm({
    name: '',
    description: '',
    unit_id: props.units.length ? props.units[0].id : null,
    file_path: null,
});

function submit() {
    form
        .transform((data) => {
            const payload = { ...data };
            if (!payload.file_path) delete payload.file_path; // don't send null file
            return payload;
        })
        .post(route('resources.store'), {
            forceFormData: true,
        });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Crear Recurso" />
        <template #header>
            <PageHeader title="Recursos" subtitle="Crea un nuevo recurso." icon="fa-solid fa-folder-open" :breadcrumbs="[
                { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                { label: 'Recursos', href: route('resources.index') },
                { label: 'Crear' }
            ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('resources.index')">
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
                    <form @submit.prevent="submit" enctype="multipart/form-data">
                        <Form :form="form" :units="props.units" submitText="Crear" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
