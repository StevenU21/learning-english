<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import PageHeader from '@/Components/PageHeader.vue';
import CardSection from '@/Components/CardSection.vue';
import TopTable from '@/Components/TopTable.vue';

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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <Card v-for="card in [
                    { label: 'Estudiantes', value: props.stats.students, icon: 'fa-user-graduate', gradient: 'from-blue-500/10 to-blue-500/5', iconColor: 'text-blue-500' },
                    { label: 'Ejercicios', value: props.stats.exercises, icon: 'fa-pencil', gradient: 'from-green-500/10 to-green-500/5', iconColor: 'text-green-500' },
                    { label: 'Unidades', value: props.stats.units, icon: 'fa-layer-group', gradient: 'from-purple-500/10 to-purple-500/5', iconColor: 'text-purple-500' },
                    { label: 'Lecciones', value: props.stats.lessons, icon: 'fa-book', gradient: 'from-pink-500/10 to-pink-500/5', iconColor: 'text-pink-500' },
                    { label: 'Horas de aprendizaje', value: props.stats.learning_hours, icon: 'fa-clock', gradient: 'from-indigo-500/10 to-indigo-500/5', iconColor: 'text-indigo-500' },
                    { label: 'Aciertos globales (%)', value: props.stats.global_accuracy, icon: 'fa-percent', gradient: 'from-yellow-500/10 to-yellow-500/5', iconColor: 'text-yellow-500' },
                    { label: 'Intentos totales', value: props.stats.total_attempts, icon: 'fa-bolt', gradient: 'from-orange-500/10 to-orange-500/5', iconColor: 'text-orange-500' },
                    { label: 'Estudiantes que completaron todas las unidades (%)', value: props.stats.students_completed_all_units, icon: 'fa-check-double', gradient: 'from-green-500/10 to-green-500/5', iconColor: 'text-green-500' }
                ]" :key="card.label" :label="card.label" :value="card.value" :icon="card.icon"
                    :gradient="card.gradient" :iconColor="card.iconColor" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <TopTable title="Top 5 estudiantes por horas"
                    :columns="[{ label: 'Estudiante', key: 'name' }, { label: 'Horas', key: 'hours', align: 'right' }]"
                    :items="props.stats.top_students_hours" />
                <TopTable title="Top 5 estudiantes por lecciones"
                    :columns="[{ label: 'Estudiante', key: 'name' }, { label: 'Lecciones', key: 'lessons', align: 'right' }]"
                    :items="props.stats.top_students_lessons" />
                <TopTable title="Top 5 ejercicios con más errores"
                    :columns="[{ label: 'Ejercicio', key: 'exercise' }, { label: 'Errores', key: 'errors', align: 'right' }]"
                    :items="props.stats.top_exercises_errors" />
                <TopTable title="Top 3 lecciones más populares"
                    :columns="[{ label: 'Lección', key: 'lesson' }, { label: 'Completadas', key: 'completions', align: 'right' }]"
                    :items="props.stats.top_lessons_popular" />
                <TopTable title="Top 3 estudiantes con mayor precisión"
                    :columns="[{ label: 'Estudiante', key: 'name' }, { label: 'Precisión (%)', key: 'accuracy', align: 'right' }]"
                    :items="props.stats.top_students_accuracy" />
                <TopTable title="Top 3 ejercicios más realizados"
                    :columns="[{ label: 'Ejercicio', key: 'exercise' }, { label: 'Intentos', key: 'attempts', align: 'right' }]"
                    :items="props.stats.top_exercises_most_attempted" />
            </div>
        </CardSection>
    </AuthenticatedLayout>
</template>
