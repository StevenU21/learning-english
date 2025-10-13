<script setup>
import { defineProps, defineEmits } from 'vue'
import GenericModal from '@/Components/GenericModal.vue'

const props = defineProps({
    exercise: { type: Object, required: true },
    attempts: { type: Array, default: () => [] }
})

const emit = defineEmits(['close'])
const close = () => emit('close')

function isPairList(arr, keyA, keyB) {
    return Array.isArray(arr) && arr.length > 0 && arr.every(
        (o) => o && typeof o === 'object' && keyA in o && keyB in o
    )
}

function fmt(val) {
    if (val === null || val === undefined) return '-'

    // Arrays
    if (Array.isArray(val)) {
        // Emparejar definiciones: [{ concepto, definicion }]
        if (isPairList(val, 'concepto', 'definicion')) {
            return val.map((o) => `${o.concepto} — ${o.definicion}`).join('; ')
        }
        // Relacionar columnas: [{ left, right }]
        if (isPairList(val, 'left', 'right')) {
            return val.map((o) => `${o.left} = ${o.right}`).join('; ')
        }
        // Array de primitivos u otros objetos
        return val
            .map((item) => {
                if (item && typeof item === 'object') {
                    if ('concepto' in item && 'definicion' in item) {
                        return `${item.concepto} — ${item.definicion}`
                    }
                    if ('left' in item && 'right' in item) {
                        return `${item.left} = ${item.right}`
                    }
                    return JSON.stringify(item)
                }
                return String(item)
            })
            .join(', ')
    }

    // Objetos
    if (typeof val === 'object') {
        if ('concepto' in val && 'definicion' in val) {
            return `${val.concepto} — ${val.definicion}`
        }
        if ('left' in val && 'right' in val) {
            return `${val.left} = ${val.right}`
        }
        // Map genérico clave:valor
        const entries = Object.entries(val)
        if (entries.length) {
            return entries.map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : String(v)}`).join('; ')
        }
        return '-'
    }

    // Primitivos
    return String(val)
}
</script>

<template>
    <GenericModal @close="close">
        <template #header>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                <i class="fa-solid fa-list-check mr-2"></i>
                Intentos del ejercicio: <span class="font-bold">{{ exercise?.prompt }}</span>
            </h3>
        </template>

        <div v-if="attempts.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
            No hay intentos para este ejercicio.
        </div>
        <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-900/70">
                    <tr class="text-left">
                        <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Ejercicio</th>
                        <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Intento</th>
                        <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Solución</th>
                        <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Respuesta del usuario</th>
                        <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Respondido</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in attempts" :key="a.id" class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200 truncate max-w-xs"
                            :title="exercise?.prompt">
                            {{ exercise?.prompt?.slice(0, 80) }}<span v-if="exercise?.prompt?.length > 80">…</span>
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">#{{ a.attempt_number }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ fmt(exercise?.solution) }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ fmt(a.answer_given) }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ a.answered_at ? new
                            Date(a.answered_at).toLocaleString() : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <template #footer>
            <button @click="close"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow">
                Cerrar
            </button>
        </template>
    </GenericModal>
</template>

<style scoped></style>
