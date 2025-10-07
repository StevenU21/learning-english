<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Form from './Form.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({ lesson: Object, units: Array });
const form = useForm({
    name: props.lesson.name || '',
    description: props.lesson.description || '',
    unit_id: props.lesson.unit_id || null,
    image: null,
});

function submit() {
    // Enfoque similar a Units: multipart, _method spoof, y no enviar imagen si es null
    form
        .transform((data) => {
            const payload = { ...data, _method: 'put' };
            if (!payload.image) delete payload.image;
            return payload;
        })
        .post(route('lessons.update', props.lesson.id), {
            forceFormData: true,
        });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Editar Lección" />
        <template #header>
            <PageHeader title="Lecciones" subtitle="Edita la lección seleccionada." icon="fa-solid fa-book-open"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Lecciones', href: route('lessons.index') },
                    { label: 'Editar' }
                ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('lessons.index')">
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
                        <Form :form="form" :units="props.units" submitText="Actualizar" />
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
