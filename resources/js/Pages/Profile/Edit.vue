<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateUserForm from './Partials/UpdateUserForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Choose layout based on role: students use StudentLayout (no sidebar), admins use AuthenticatedLayout
const page = usePage();
const roles = computed(() => page.props.auth.user?.roles || []);
const isAdmin = computed(() => roles.value.includes('admin'));
const layoutComponent = computed(() => (isAdmin.value ? AuthenticatedLayout : StudentLayout));
</script>

<template>

    <Head title="Perfil" />

    <component :is="layoutComponent">
        <template v-if="isAdmin" #header>
            <PageHeader title="Perfil" subtitle="Configuración de tu cuenta" icon="fa-solid fa-user" :breadcrumbs="[
                { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                { label: 'Perfil' }
            ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('student.units.index')"
                        class="inline-flex w-34 items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Inicio
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-10">
            <div class="w-full space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700">
                    <UpdateUserForm />
                </div>
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700">
                    <UpdatePasswordForm />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </component>
</template>
