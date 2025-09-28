<template>
    <AuthenticatedLayout>

        <Head :title="'Imagen de Unidad'" />
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 flex flex-col items-center">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 mb-6">Imagen de la Unidad</h2>
                    <img v-if="unit.image_url || unit.image" :src="unit.image_url || unit.image"
                        alt="Imagen de la unidad"
                        class="w-64 h-64 object-contain rounded shadow border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 mb-4" />
                    <span v-else class="text-gray-400 italic">Sin imagen</span>
                    <div class="mt-6">
                        <Link :href="route('units.index')">
                        <PrimaryButton><i class="fa-solid fa-arrow-left mr-2"></i>Volver</PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({ unit: Object });
</script>
