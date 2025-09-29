<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>

    <Head title="Bienvenido" />
    <div class="bg-gray-50 text-black/50 dark:bg-gray-900 dark:text-white/50 min-h-screen flex flex-col">
        <div
            class="relative flex flex-col flex-1 items-center justify-center selection:bg-blue-500 selection:text-white px-6">

            <!-- Hero Card -->
            <div class="w-full max-w-2xl">
                <div
                    class="rounded-2xl bg-white/80 dark:bg-gray-900 ring-1 ring-black/10 dark:ring-white/10 shadow-xl p-10 text-center backdrop-blur">
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Bienvenido a <span class="text-[#FF2D20]">Learning English</span>
                    </h1>
                    <p class="mt-6 text-base sm:text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                        Plataforma para gestionar unidades, lecciones, recursos y tu progreso de aprendizaje del inglés.
                    </p>
                    <div v-if="!$page.props.auth.user"
                        class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link :href="route('login')"
                            class="w-full sm:w-auto rounded-lg px-6 py-3 text-sm font-semibold text-white bg-white ring-1 ring-blue-500/40 hover:ring-blue-500 dark:bg-transparent dark:text-white dark:ring-white/30 dark:hover:ring-white/60 transition">
                        Iniciar sesión</Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="w-full sm:w-auto rounded-lg px-6 py-3 text-sm font-semibold text-blue-500 bg-white ring-1 ring-blue-500/40 hover:ring-blue-500 dark:bg-transparent dark:text-white dark:ring-white/30 dark:hover:ring-white/60 transition">
                        Crear cuenta</Link>
                    </div>
                    <div v-else class="mt-8">
                        <Link :href="route('levels.index')"
                            class="inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 shadow-md transition">
                        Ir al Panel
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 12h15m0 0-6.75-6.75M19.5 12l-6.75 6.75" />
                        </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sub info -->
            <footer class="mt-16 text-center text-xs text-gray-500 dark:text-gray-400">
                Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
            </footer>
        </div>
    </div>
</template>
