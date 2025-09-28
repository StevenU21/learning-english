<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
    lessons: {
        type: [Object, Array],
        required: true
    }
});

const lessonList = computed(() => {
    const u = props.lessons;
    return Array.isArray(u) ? u : (u?.data ?? []);
});

function deleteLesson(id) {
    if (!confirm('¿Estás seguro?')) return;

    router.delete(route('lessons.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Lecciones" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Lecciones
                </h2>
                <Link :href="route('lessons.create')">
                <PrimaryButton>
                    <i class="fa-solid fa-plus mr-2"></i> Agregar Lección
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max">
                            <thead class="bg-gray-200 dark:bg-gray-900">
                                <tr>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-id-badge mr-2"></i>ID</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-font mr-2"></i>Nombre</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-layer-group mr-2"></i>Unidad</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4 text-left"><i
                                            class="fa-solid fa-align-left mr-2"></i>Descripción</th>
                                    <th class="text-gray-800 dark:text-gray-200 p-4"><i
                                            class="fa-solid fa-cogs mr-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="lessonList.length === 0">
                                    <td colspan="5"
                                        class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        No se encontraron lecciones.
                                    </td>
                                </tr>
                                <tr v-for="lesson in lessonList" :key="lesson.id"
                                    class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ lesson.id }}</td>
                                    <td class="text-gray-800 dark:text-gray-200 px-4 py-2">{{ lesson.name }}</td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">{{ lesson.unit?.name || '' }}
                                    </td>
                                    <td class="text-gray-600 dark:text-gray-400 px-4 py-2">
                                        {{ lesson.description && lesson.description.length > 12 ?
                                            lesson.description.slice(0,
                                        12) + '…' : lesson.description }}
                                    </td>
                                    <td class="px-4 py-2 space-x-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link :href="route('lessons.show', lesson.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-eye mr-2"></i> Ver</PrimaryButton>
                                            </Link>
                                            <Link :href="route('lessons.edit', lesson.id)">
                                            <PrimaryButton class="bg-red-500 hover:bg-red-700 text-white"><i
                                                    class="fa-solid fa-pen-to-square mr-2"></i> Editar</PrimaryButton>
                                            </Link>
                                            <PrimaryButton @click="deleteLesson(lesson.id)"
                                                class="bg-red-500 hover:bg-red-700 text-white">
                                                <i class="fa-solid fa-trash mr-2"></i> Eliminar
                                            </PrimaryButton>
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
