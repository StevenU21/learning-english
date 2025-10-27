<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import CardSection from '@/Components/CardSection.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Dashboard" subtitle="Resumen y estadísticas del sitio de aprendizaje."
                icon="fa-solid fa-chart-pie" :breadcrumbs="[
                    { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                    { label: 'Dashboard' }
                ]" gradient-classes="from-purple-600 to-indigo-600" />
        </template>

        <CardSection>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card v-for="card in [
                    { label: 'Estudiantes', value: props.stats.students, icon: 'fa-user-graduate', gradient: 'from-blue-500/10 to-blue-500/5', iconColor: 'text-blue-500' },
                    { label: 'Admins', value: props.stats.admins, icon: 'fa-user-shield', gradient: 'from-indigo-500/10 to-indigo-500/5', iconColor: 'text-indigo-500' },
                    { label: 'Unidades', value: props.stats.units, icon: 'fa-layer-group', gradient: 'from-purple-500/10 to-purple-500/5', iconColor: 'text-purple-500' },
                    { label: 'Lecciones', value: props.stats.lessons, icon: 'fa-book', gradient: 'from-pink-500/10 to-pink-500/5', iconColor: 'text-pink-500' },
                    { label: 'Ejercicios', value: props.stats.exercises, icon: 'fa-pencil', gradient: 'from-green-500/10 to-green-500/5', iconColor: 'text-green-500' },
                    { label: 'Tipos de Ejercicio', value: props.stats.exercise_types, icon: 'fa-shapes', gradient: 'from-yellow-500/10 to-yellow-500/5', iconColor: 'text-yellow-500' },
                    { label: 'Recursos', value: props.stats.resources, icon: 'fa-box-archive', gradient: 'from-orange-500/10 to-orange-500/5', iconColor: 'text-orange-500' },
                    { label: 'Intentos', value: props.stats.attempts, icon: 'fa-chart-line', gradient: 'from-red-500/10 to-red-500/5', iconColor: 'text-red-500' }
                ]" :key="card.label" :label="card.label" :value="card.value" :icon="card.icon"
                    :gradient="card.gradient" :iconColor="card.iconColor" />
            </div>
        </CardSection>
    </AuthenticatedLayout>
</template>
