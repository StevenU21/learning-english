<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400 bg-green-400/10 p-3 rounded-lg border border-green-400/20">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Correo electrónico" class="text-gray-300" />
                <TextInput id="email" type="email" class="mt-1 block w-full bg-gray-900/50 border-gray-600/50 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 transition-colors" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-gray-300" />
                <TextInput id="password" type="password" class="mt-1 block w-full bg-gray-900/50 border-gray-600/50 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 transition-colors" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="bg-gray-900/50 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900" />
                    <span class="ms-2 text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Recuérdame</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <div>
                <button type="submit" :disabled="form.processing"
                    :class="['w-full flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:from-indigo-500 hover:to-purple-500 hover:shadow-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900 active:scale-[0.98]', { 'opacity-70 cursor-not-allowed': form.processing }]">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </button>
            </div>

            <div class="mt-4">
                <div class="relative flex items-center py-1">
                    <div class="flex-grow border-t border-gray-700/50"></div>
                    <span class="mx-4 text-gray-500 text-xs font-medium">o continúa con</span>
                    <div class="flex-grow border-t border-gray-700/50"></div>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a :href="route('auth.google.redirect')"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-white/10 transition-all hover:bg-white/10 hover:text-white">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="h-4 w-4" />
                        Google
                    </a>
                    <a href="#"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-white/10 transition-all hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <a href="#"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-white/10 transition-all hover:bg-white/10 hover:text-white">
                        <svg class="h-4 w-4 fill-current text-pink-500" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        Instagram
                    </a>
                </div>
            </div>
        </form>

        <div class="mt-5 text-center">
            <p class="text-sm text-gray-400">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                    Regístrate
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
