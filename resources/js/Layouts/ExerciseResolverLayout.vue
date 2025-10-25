<template>
    <div class="exercise-resolver-layout min-h-screen bg-gray-100 dark:bg-gray-800 flex flex-col">
        <!-- Botón X para salir en móvil -->
        <div class="absolute left-0 top-0 pt-6 pl-4 z-20 block sm:hidden">
            <button @click="handleExit"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-gray-900 text-white text-xl shadow">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Barra de progreso arriba -->
        <div class="w-full flex justify-center pt-8 pb-4">
            <ExerciseProgressBar :value="progress" class="w-full max-w-[98vw] sm:max-w-4xl sm:w-[60vw]" />
        </div>
        <!-- Ejercicio en el centro -->
        <main class="flex-1 flex flex-col items-center justify-center w-full sm:w-auto px-2 sm:px-4">
            <slot />
        </main>
        <!-- Botones de acción abajo -->
        <div class="w-full flex flex-col items-center justify-center pb-12">
            <slot name="actions" />
        </div>
        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres salir?
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showConfirm = false"
                        class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-800 text-gray-200 font-semibold">Cancelar</button>
                    <button @click="confirmExit"
                        class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">Salir sin
                        guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ExerciseProgressBar from '@/Components/ExerciseProgressBar.vue';

const props = defineProps({
    progress: { type: Number, required: true }
});

const showConfirm = ref(false);

function handleExit() {
    showConfirm.value = true;
}

function confirmExit() {
    showConfirm.value = false;
    router.get(route('student.units.index'));
}
</script>

<style scoped>
.exercise-resolver-layout {
    font-family: 'Inter', sans-serif;
}
</style>
