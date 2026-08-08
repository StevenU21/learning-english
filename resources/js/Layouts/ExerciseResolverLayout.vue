<template>
    <div class="relative exercise-resolver-layout min-h-screen bg-[#0B1120] text-gray-100 flex flex-col selection:bg-indigo-500 selection:text-white">
        <!-- Background glows -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[20%] -right-[10%] w-[500px] h-[500px] rounded-full bg-indigo-600/20 blur-[120px]"></div>
            <div class="absolute top-[60%] -left-[10%] w-[400px] h-[400px] rounded-full bg-purple-600/20 blur-[100px]"></div>
        </div>

        <div class="relative z-10 w-full flex justify-center pt-8 pb-4 px-3 sm:px-0 sm:max-w-2xl mx-auto">
            <ExerciseProgressBar :value="progress" class="w-full h-8" />
        </div>
        <main class="relative z-10 flex-1 flex flex-col items-center justify-center w-full sm:w-auto px-2 sm:px-4">
            <slot />
        </main>
        <div class="relative z-10 w-full flex flex-col items-center justify-center pb-12">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import ExerciseProgressBar from '@/Components/ExerciseProgressBar.vue';

const props = defineProps({
    progress: { type: Number, required: true }
});
</script>

<style scoped>
.exercise-resolver-layout {
    font-family: 'Inter', sans-serif;
}
</style>
