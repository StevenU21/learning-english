<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-800 pt-6 sm:justify-center sm:pt-0">
        <div class="text-center flex flex-col items-center mt-4 mb-2">
            <Link href="/">
            <img src="/img/logo02.png" alt="NATIVO Logo" class="w-16 h-16 sm:w-24 sm:h-24 mx-auto" />
            <h1 class="text-2xl sm:text-3xl font-nativo text-gray-900 dark:text-white tracking-wide mb-1">
                <span class="block">N A T I V O</span>
            </h1>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg border border-solid border-gray-400/40 dark:bg-gray-800 dark:border-gray-600">
            <slot />
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
