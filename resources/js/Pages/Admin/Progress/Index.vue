<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    units: Array,
    users: Array,
    lessons: Array,
    progress: Array,
    selectedUnit: [String, Number],
    selectedUser: [String, Number],
    selectedLesson: [String, Number],
    selectedStatus: String,
});

const {
    units,
    users,
    lessons,
    progress: progressList,
    selectedUnit,
    selectedUser,
    selectedLesson,
    selectedStatus,
} = props;

const form = useForm({
    unit_id: selectedUnit || '',
    user_id: selectedUser || '',
    lesson_id: selectedLesson || '',
    status: selectedStatus || '',
});

function applyFilters() {
    form.get(route('admin.progress.index'), { preserveState: true, replace: true });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Progreso" />

        <template #header>
            <div class="flex flex-col space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Progreso</h2>
                </div>
                <div class="flex space-x-4">
                    <div>
                        <SelectInput v-model="form.unit_id" @change="applyFilters" class="w-60">
                            <option value="">Todas las Unidades</option>
                            <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <SelectInput v-model="form.user_id" @change="applyFilters" class="w-60">
                            <option value="">Todos los Usuarios</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <SelectInput v-model="form.lesson_id" @change="applyFilters" class="w-60">
                            <option value="">Todas las Lecciones</option>
                            <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">{{ lesson.name }}
                            </option>
                        </SelectInput>
                    </div>
                    <div>
                        <SelectInput v-model="form.status" @change="applyFilters" class="w-60">
                            <option value="">Todos los Estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En Progreso</option>
                            <option value="completado">Completado</option>
                        </SelectInput>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max">
                            <thead class="bg-gray-200 dark:bg-gray-900">
                                <tr>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Usuario</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Unidad</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Lección</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Progreso</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="progressList.length === 0">
                                    <td colspan="5"
                                        class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron registros de progreso.
                                    </td>
                                </tr>
                                <tr v-for="item in progressList" :key="item.id"
                                    class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ item.user.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ item.lesson.unit.name }}
                                    </td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ item.lesson.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ item.progress }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2 capitalize">{{ item.status }}
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
