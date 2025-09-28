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

    <Head title="Crear Unidad" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Crear Unidad</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('units.index')"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white font-semibold">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                    </Link>
                </div>
                <Form :form="form" :levels="props.levels" submitText="Crear" :onSubmit="submit" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
