<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen py-4 flex flex-col items-center justify-center bg-[#0B1120] text-gray-100 selection:bg-indigo-500 selection:text-white overflow-y-auto">
        <!-- Background glows -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[20%] -right-[10%] w-[500px] h-[500px] rounded-full bg-indigo-600/20 blur-[120px]"></div>
            <div class="absolute top-[60%] -left-[10%] w-[400px] h-[400px] rounded-full bg-purple-600/20 blur-[100px]"></div>
        </div>

        <div class="relative z-10 w-full sm:max-w-md px-4 flex flex-col items-center my-auto">
            <!-- Logo area -->
            <div class="text-center flex flex-col items-center mb-4">
                <Link href="/" class="group flex flex-col items-center transition-all duration-300 hover:scale-105">
                    <div class="relative">
                        <div class="absolute inset-0 bg-indigo-500/20 rounded-full blur-xl scale-110 group-hover:bg-indigo-500/40 transition-colors duration-300"></div>
                        <img src="/img/logo02.png" alt="NATIVO Logo" class="relative w-14 h-14 sm:w-16 sm:h-16 mx-auto object-contain drop-shadow-2xl" />
                    </div>
                    <h1 class="mt-2 text-xl sm:text-2xl font-nativo text-white tracking-widest drop-shadow-md">
                        N A T I V O
                    </h1>
                </Link>
            </div>

            <!-- Card -->
            <div class="w-full overflow-hidden bg-gray-900/60 backdrop-blur-xl px-6 py-6 sm:px-8 sm:py-6 shadow-[0_8px_30px_rgb(0,0,0,0.4)] sm:rounded-2xl border border-white/10">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
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
