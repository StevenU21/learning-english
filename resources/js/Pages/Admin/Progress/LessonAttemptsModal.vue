<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    lesson: { type: Object, required: true },
    attempts: { type: Array, default: () => [] }
})

const emit = defineEmits(['close'])

function close() {
    emit('close')
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="close" />

        <!-- Modal -->
        <div
            class="relative w-full max-w-4xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 overflow-y-auto max-h-[80vh]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    <i class="fa-solid fa-list-check mr-2"></i>
                    Intentos de la lección: <span class="font-bold">{{ lesson?.name }}</span>
                </h3>
                <button @click="close"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div v-if="attempts.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                    No hay intentos registrados para esta lección.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-900/70">
                            <tr class="text-left">
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                        class="fa-solid fa-file-lines mr-2"></i>Ejercicio</th>
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                        class="fa-solid fa-pen-to-square mr-2"></i>Intento</th>
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                        class="fa-solid fa-check mr-2"></i>Correcto</th>
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                        class="fa-solid fa-hourglass-start mr-2"></i>Iniciado</th>
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                        class="fa-solid fa-calendar-check mr-2"></i>Respondido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in attempts" :key="a.id"
                                class="border-b border-gray-100 dark:border-gray-700/50">
                                <td class="px-4 py-2 text-gray-800 dark:text-gray-200 truncate max-w-xs"
                                    :title="a.exercise?.prompt">
                                    {{ a.exercise?.prompt?.slice(0, 80) }}<span
                                        v-if="a.exercise?.prompt?.length > 80">…</span>
                                </td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300">#{{ a.attempt_number }}</td>
                                <td class="px-4 py-2">
                                    <span
                                        :class="['px-2 py-1 rounded text-xs font-medium', a.is_correct ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300']">
                                        {{ a.is_correct ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ a.started_at ? new
                                    Date(a.started_at).toLocaleString() : '-' }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ a.answered_at ? new
                                    Date(a.answered_at).toLocaleString() : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button @click="close"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
