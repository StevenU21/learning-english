<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    user: Object,
    units: Array,
    lessons: Array,
    lessonProgress: Array,
    unitProgress: Array,
    attempts: Array,
});

const { user, lessonProgress, unitProgress, attempts, units, lessons } = props;

// Filtros
const unitStatusFilter = ref('');
const lessonUnitFilter = ref('');
const lessonStatusFilter = ref('');
const attemptUnitFilter = ref('');
const attemptLessonFilter = ref('');
const attemptCorrectFilter = ref(''); // '', '1', '0'
const attemptSearch = ref('');

// Computed filtrados
const filteredUnitProgress = computed(() => {
    return unitProgress.filter(up => {
        if (unitStatusFilter.value && up.status !== unitStatusFilter.value) return false;
        return true;
    });
});

const filteredLessonProgress = computed(() => {
    return lessonProgress.filter(lp => {
        if (lessonUnitFilter.value && String(lp.lesson.unit.id) !== String(lessonUnitFilter.value)) return false;
        if (lessonStatusFilter.value && lp.status !== lessonStatusFilter.value) return false;
        return true;
    });
});

const filteredAttempts = computed(() => {
    return attempts.filter(att => {
        const lesson = att.lesson || att.exercise?.lesson;
        const unitId = lesson?.unit?.id;
        if (attemptUnitFilter.value && String(unitId) !== String(attemptUnitFilter.value)) return false;
        if (attemptLessonFilter.value && String(lesson?.id) !== String(attemptLessonFilter.value)) return false;
        if (attemptCorrectFilter.value !== '' && String(att.is_correct ? 1 : 0) !== attemptCorrectFilter.value) return false;
        if (attemptSearch.value) {
            const text = (att.exercise?.prompt || '').toLowerCase();
            if (!text.includes(attemptSearch.value.toLowerCase())) return false;
        }
        return true;
    });
});

function statusBadgeClasses(status) {
    switch (status) {
        case 'completado':
            return 'bg-green-100 text-green-800 dark:bg-green-600/20 dark:text-green-300';
        case 'en_progreso':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-600/20 dark:text-blue-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-600/30 dark:text-gray-300';
    }
}
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`Progreso - ${user.name}`" />
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Progreso del
                        Estudiante</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Detalle completo del avance de <span
                            class="font-medium">{{ user.name }}</span></p>
                </div>
                <div class="flex space-x-2">
                    <Link :href="route('admin.progress.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Volver
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
                <!-- Resumen del usuario -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Información del Estudiante
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Nombre</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ user.name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Email</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ user.email }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Creado</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ new
                                Date(user.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Progreso por Unidad -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Progreso por Unidad</h3>
                        <div class="flex space-x-3 text-sm">
                            <select v-model="unitStatusFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Estado: Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900/70">
                                <tr class="text-left">
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Unidad</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Progreso</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredUnitProgress.length === 0">
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin
                                        registros
                                    </td>
                                </tr>
                                <tr v-for="up in filteredUnitProgress" :key="up.id"
                                    class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ up.unit.name }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ up.progress }}%</td>
                                    <td class="px-4 py-2">
                                        <span
                                            :class="['px-2 py-1 rounded text-xs font-medium', statusBadgeClasses(up.status)]">{{
                                            up.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Progreso por Lección -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Progreso por Lección</h3>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <select v-model="lessonUnitFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Unidad: Todas</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <select v-model="lessonStatusFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Estado: Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900/70">
                                <tr class="text-left">
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Unidad</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Lección</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Progreso</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredLessonProgress.length === 0">
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin
                                        registros
                                    </td>
                                </tr>
                                <tr v-for="lp in filteredLessonProgress" :key="lp.id"
                                    class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ lp.lesson.unit.name }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ lp.lesson.name }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ lp.progress }}%</td>
                                    <td class="px-4 py-2">
                                        <span
                                            :class="['px-2 py-1 rounded text-xs font-medium', statusBadgeClasses(lp.status)]">{{
                                            lp.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Intentos de Ejercicios -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Intentos de Ejercicios</h3>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <select v-model="attemptUnitFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Unidad: Todas</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <select v-model="attemptLessonFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Lección: Todas</option>
                                <option v-for="l in lessons" :key="l.id" :value="l.id">{{ l.name }}</option>
                            </select>
                            <select v-model="attemptCorrectFilter"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Resultado: Todos</option>
                                <option value="1">Correctos</option>
                                <option value="0">Incorrectos</option>
                            </select>
                            <input v-model="attemptSearch" type="text" placeholder="Buscar ejercicio..."
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900/70">
                                <tr class="text-left">
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Ejercicio</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Lección</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Intento</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Correcto</th>
                                    <th class="px-4 py-3 text-gray-700 dark:text-gray-300">Respondido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredAttempts.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin
                                        intentos
                                        registrados</td>
                                </tr>
                                <tr v-for="att in filteredAttempts" :key="att.id"
                                    class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="px-4 py-2 text-gray-800 dark:text-gray-200 truncate max-w-xs"
                                        :title="att.exercise?.prompt">{{ att.exercise?.prompt?.slice(0, 60) }}<span
                                            v-if="att.exercise?.prompt?.length > 60">...</span></td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ att.lesson?.name ||
                                        att.exercise?.lesson?.name }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">#{{ att.attempt_number }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span
                                            :class="['px-2 py-1 rounded text-xs font-medium', att.is_correct ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300']">{{
                                            att.is_correct ? 'Sí' : 'No' }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ att.answered_at ? new
                                        Date(att.answered_at).toLocaleString() : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
