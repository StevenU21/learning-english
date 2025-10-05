<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import { computed } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    resources: { type: Array, required: true }
});

const hasResources = computed(() => props.resources.length > 0);
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="'Recursos - ' + unit.name" />
        <template #header>
            <PageHeader
                :title="`Recursos de ${unit.name}`"
                subtitle="Descarga materiales y archivos."
                icon="fa-solid fa-book"
                :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Unidades', href: route('student.units.index') },
                    { label: 'Recursos' }
                ]"
                gradient-classes="from-purple-600 to-indigo-600"
            >
                <template #actions>
                    <Link :href="route('student.units.index')"
                        class="inline-flex w-34 items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="!hasResources"
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col items-center gap-3">
                        <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                        No hay recursos para esta unidad.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="r in resources" :key="r.id"
                        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <h3
                                class="font-semibold text-gray-800 dark:text-gray-200 text-sm leading-tight line-clamp-2">
                                {{
                                    r.name }}</h3>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 flex-1 leading-relaxed line-clamp-4 mb-4">{{
                            r.description || 'Sin descripción' }}</p>
                        <div class="mt-auto flex flex-col gap-2">
                            <Link :href="route('student.resources.download', r.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fa-solid fa-download mr-2"></i> Descargar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
