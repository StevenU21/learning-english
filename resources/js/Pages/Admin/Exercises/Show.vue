<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    permissions: { type: Object, required: false, default: () => ({}) }
});

const { exercise } = props;

function isArray(val) { return Array.isArray(val); }
function prettyJSON(val) { try { return JSON.stringify(val, null, 2); } catch { return val; } }
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`Ejercicio #${exercise.id}`" />
        <template #header>
            <PageHeader
                :title="`Ejercicio #${exercise.id}`"
                subtitle="Detalles del ejercicio."
                icon="fa-solid fa-pencil"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Ejercicios', href: route('exercises.index') },
                    { label: 'Detalle' }
                ]"
                gradient-classes="from-purple-600 to-indigo-600"
            >
                <template #actions>
                    <div class="space-x-2 flex">
                        <Link :href="route('exercises.index')">
                            <PrimaryButton>
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Volver a la lista
                            </PrimaryButton>
                        </Link>
                        <Link v-if="permissions?.update" :href="route('exercises.edit', exercise.id)">
                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                Editar
                            </PrimaryButton>
                        </Link>
                        <PrimaryButton v-if="permissions?.destroy"
                            @click="confirm('¿Estás seguro de eliminar este ejercicio?') && $inertia.delete(route('exercises.destroy', exercise.id))"
                            class="bg-red-500 hover:bg-red-700 text-white">
                            <i class="fa-solid fa-trash mr-2"></i>
                            Eliminar
                        </PrimaryButton>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="py-0">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max rounded-xl overflow-hidden border border-gray-600">
                            <tbody>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-font mr-2"></i>Tipo
                                    </th>
                                    <td class="p-4 text-gray-300 font-semibold">{{ exercise.exercise_type?.name || '-'
                                        }}</td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-layer-group mr-2"></i>Lección
                                    </th>
                                    <td class="p-4">
                                        <span v-if="exercise.lesson?.name"
                                            class="inline-block bg-gray-600/80 text-gray-300 px-3 py-1 rounded-full text-sm font-medium shadow-sm">
                                            {{ exercise.lesson.name }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-align-left mr-2"></i>Enunciado
                                    </th>
                                    <td class="p-4 text-gray-300">{{ exercise.prompt }}</td>
                                </tr>
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition"
                                    v-if="exercise.explanation">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-circle-info mr-2"></i>Explicación
                                    </th>
                                    <td class="p-4 text-gray-300 italic">{{ exercise.explanation }}</td>
                                </tr>
                                <!-- Opciones -->
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition"
                                    v-if="exercise.options && exercise.options.length">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-list-ul mr-2"></i>Opciones
                                    </th>
                                    <td class="p-4 text-gray-300">
                                        <ul class="list-disc list-inside space-y-1"
                                            v-if="Array.isArray(exercise.options) && exercise.options.every(o => typeof o !== 'object')">
                                            <li v-for="(opt, i) in exercise.options" :key="i">{{ opt }}</li>
                                        </ul>
                                        <div v-else
                                            class="text-xs font-mono whitespace-pre-wrap bg-gray-900/60 p-3 rounded border border-gray-700">
                                            {{ prettyJSON(exercise.options) }}</div>
                                    </td>
                                </tr>
                                <!-- Solución -->
                                <tr class="border-b border-gray-600 hover:bg-gray-600/40 transition"
                                    v-if="exercise.solution && exercise.solution.length">
                                    <th class="text-left p-4 text-gray-200 w-48 align-top">
                                        <i class="fa-solid fa-key mr-2"></i>Solución
                                    </th>
                                    <td class="p-4 text-gray-300">
                                        <!-- Caso solución simple lista -->
                                        <ul class="list-decimal list-inside space-y-1"
                                            v-if="Array.isArray(exercise.solution) && exercise.solution.every(s => typeof s !== 'object')">
                                            <li v-for="(s, i) in exercise.solution" :key="i">{{ s }}</li>
                                        </ul>
                                        <!-- Caso objetos (pares, matches, diálogo) -->
                                        <div v-else class="space-y-2">
                                            <div v-for="(row, i) in exercise.solution" :key="i"
                                                class="bg-gray-900/50 rounded px-3 py-2 text-sm border border-gray-700">
                                                <template v-if="row.left !== undefined && row.right !== undefined">
                                                    <span class="text-indigo-300 font-medium">{{ row.left }}</span>
                                                    <span class="text-gray-400"> → </span>
                                                    <span class="text-pink-300">{{ row.right }}</span>
                                                </template>
                                                <template
                                                    v-else-if="row.concepto !== undefined && row.definicion !== undefined">
                                                    <span class="text-indigo-300 font-medium">{{ row.concepto }}</span>
                                                    <span class="text-gray-400"> → </span>
                                                    <span class="text-pink-300">{{ row.definicion }}</span>
                                                </template>
                                                <template v-else>
                                                    <code class="text-xs">{{ prettyJSON(row) }}</code>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
