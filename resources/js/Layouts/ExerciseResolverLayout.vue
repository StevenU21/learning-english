<template>
    <div class="exercise-resolver-layout min-h-screen bg-gray-100 dark:bg-gray-800 flex flex-col">
        <!-- Botón X eliminado -->
        <!-- Barra de progreso arriba -->
        <div class="w-full flex justify-center pt-8 pb-4 px-3 sm:px-0 sm:max-w-2xl mx-auto">
            <ExerciseProgressBar :value="progress" class="w-full h-8" />
        </div>
        <!-- Ejercicio en el centro -->
        <main class="flex-1 flex flex-col items-center justify-center w-full sm:w-auto px-2 sm:px-4">
            <slot />
        </main>
        <!-- Botones de acción abajo -->
        <div class="w-full flex flex-col items-center justify-center pb-12">
            <slot name="actions" />
        </div>
        <!-- Modal de confirmación eliminado -->
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
