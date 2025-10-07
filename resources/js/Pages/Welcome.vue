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
                    class="rounded-2xl bg-white/80 dark:bg-gray-800 ring-1 ring-black/10 dark:ring-white/10 shadow-xl p-4 sm:p-8 text-center backdrop-blur flex flex-col items-center gap-2">
                    <h2 class="text-2xl sm:text-3xl font-nativo text-gray-900 dark:text-white tracking-wide mb-1">
                        Bienvenidos a
                    </h2>
                    <img src="/img/logo01.png" alt="NATIVO Logo" class="w-40 h-40 sm:w-50 sm:h-50 mb-2 mx-auto" />
                    <p
                        class="mt-1 text-sm sm:text-lg leading-relaxed text-gray-600 dark:text-gray-300 max-w-xs sm:max-w-xl mx-auto">
                        Plataforma interactiva para el aprendizaje del inglés, donde puedes avanzar a tu ritmo y mejorar
                        tus habilidades de manera práctica y divertida.
                    </p>
                    <div class="w-full flex flex-col items-center mt-3 sm:mt-6">
                        <div v-if="!$page.props.auth.user"
                            class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 w-full">
                            <Link :href="route('login')"
                                class="w-full sm:w-auto rounded-lg px-4 py-2 sm:px-6 sm:py-3 text-sm font-semibold text-blue-500 bg-white ring-1 ring-blue-500/40 hover:ring-blue-500 dark:bg-transparent dark:text-white dark:ring-white/30 dark:hover:ring-white/60 transition flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i> Iniciar sesión
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="w-full sm:w-auto rounded-lg px-4 py-2 sm:px-6 sm:py-3 text-sm font-semibold text-blue-500 bg-white ring-1 ring-blue-500/40 hover:ring-blue-500 dark:bg-transparent dark:text-white dark:ring-white/30 dark:hover:ring-white/60 transition flex items-center justify-center">
                            <i class="fas fa-user-plus mr-2"></i> Crear cuenta
                            </Link>
                        </div>
                        <div v-else class="mt-2 sm:mt-4 w-full flex justify-center">
                            <Link
                                :href="route($page.props.auth.user.roles.includes('admin') ? 'units.index' : 'student.units.index')"
                                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 sm:px-6 sm:py-3 text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 shadow-md transition">
                            Ir al Panel
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                            </Link>
                        </div>
                    </div>
                    <div
                        class="mt-4 sm:mt-8 text-center text-xs sm:text-base uppercase text-gray-500 dark:text-gray-400 font-semibold tracking-wide">
                        <div>Creado por:</div>
                        <div>Arelis Antonia Ortez Bucardo</div>
                        <div>Madeling Haidee Perez Moreno</div>
                        <div>Andy Ruppert Ulloa Gutierrez</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/****/
@font-face {
    font-family: 'SuezOne';
    src: url('/fonts/SuezOne-Regular.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
}

.font-nativo {
    font-family: 'SuezOne', serif;
    letter-spacing: 0.08em;
}
</style>
