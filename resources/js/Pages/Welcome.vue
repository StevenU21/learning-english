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
    <div class="bg-gray-50 text-black/50 dark:bg-gray-800 dark:text-white/50 min-h-screen flex flex-col">
        <div
            class="relative flex flex-col flex-1 items-center justify-center selection:bg-blue-500 selection:text-white px-6">

            <!-- Hero Card -->
            <div class="w-full max-w-2xl">
                <div
                    class="rounded-2xl bg-white/80 dark:bg-gray-800 border border-solid border-gray-400/40 dark:border-gray-600 shadow-xl p-4 sm:p-8 text-center backdrop-blur flex flex-col items-center gap-2">
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
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fas fa-sign-in-alt mr-2"></i> Iniciar sesión
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fas fa-user-plus mr-2"></i> Crear cuenta
                            </Link>
                        </div>
                        <div v-else class="mt-2 sm:mt-4 w-full flex justify-center">
                            <Link
                                :href="route($page.props.auth.user.roles.includes('admin') ? 'units.index' : 'student.units.index')"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300 gap-2">
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
                        <div>Integrantes de Tesis:</div>
                        <div>Arelis Antonia Ortez Bucardo</div>
                        <div>Madeling Haidee Perez Moreno</div>
                        <div>Andy Ruppert Ulloa Gutierrez</div>
                        <div class="mt-2">Desarrollador:</div>
                        <div>
                            <a href="https://github.com/StevenU21" target="_blank"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-xs font-semibold shadow-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-4 h-4 mr-1">
                                    <path
                                        d="M12 2C6.477 2 2 6.484 2 12.021c0 4.428 2.865 8.184 6.839 9.504.5.092.682-.217.682-.482 0-.237-.009-.868-.014-1.703-2.782.605-3.369-1.342-3.369-1.342-.454-1.154-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.004.07 1.532 1.032 1.532 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.339-2.221-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.987 1.029-2.687-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.025A9.564 9.564 0 0 1 12 6.844c.85.004 1.705.115 2.504.337 1.909-1.295 2.748-1.025 2.748-1.025.546 1.378.202 2.397.1 2.65.64.7 1.028 1.594 1.028 2.687 0 3.847-2.337 4.695-4.566 4.944.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.749 0 .267.18.578.688.48C19.138 20.2 22 16.447 22 12.021 22 6.484 17.523 2 12 2Z" />
                                </svg>
                                StevenU21
                            </a>
                        </div>
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
