<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Iniciar sesión" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Recuérdame</span>
                </label>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row items-center justify-center sm:justify-end gap-4">
                <Link :href="route('register')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800">
                <i class="fas fa-user-plus mr-2"></i> ¿No tienes cuenta? Regístrate
                </Link>

                <PrimaryButton :class="['ms-0 sm:ms-4 flex items-center', { 'opacity-25': form.processing }]"
                    :disabled="form.processing">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar sesión
                </PrimaryButton>
            </div>

            <div class="mt-6">
                <div class="relative flex items-center py-3">
                    <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                    <span class="mx-4 text-gray-500 text-sm">o continúa con</span>
                    <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                </div>
                <div class="mt-2 flex justify-center">
                    <a :href="route('auth.google.redirect')"
                        class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="h-5 w-5" />
                        Iniciar con Google
                    </a>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
