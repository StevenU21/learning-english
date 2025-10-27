<script setup>
import { Pie, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale,
    LinearScale,
    BarElement,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);


const props = defineProps({
    correctAttempts: { type: Number, default: 0 },
    incorrectAttempts: { type: Number, default: 0 },
    learningByMonth: { type: Array, default: () => [] },
    topExercises: { type: Array, default: () => [] },
});

const pieData = {
    labels: ['Aciertos', 'Errores'],
    datasets: [
        {
            data: [props.correctAttempts, props.incorrectAttempts],
            backgroundColor: ['#22c55e', '#ef4444'],
        },
    ],
};

const barData = {
    labels: Array.isArray(props.learningByMonth) ? props.learningByMonth.map((row) => row.month) : [],
    datasets: [
        {
            label: 'Horas de aprendizaje',
            data: Array.isArray(props.learningByMonth) ? props.learningByMonth.map((row) => row.hours) : [],
            backgroundColor: '#6366f1',
        },
    ],
};

const topData = {
    labels: Array.isArray(props.topExercises) ? props.topExercises.map((row) => row.exercise) : [],
    datasets: [
        {
            label: 'Intentos',
            data: Array.isArray(props.topExercises) ? props.topExercises.map((row) => row.attempts) : [],
            backgroundColor: '#f59e42',
        },
    ],
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 my-8">
        <div>
            <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-100">Aciertos vs Errores</h3>
            <Pie :data="pieData" :options="{ responsive: true, plugins: { legend: { position: 'bottom' } } }" />
        </div>
        <div>
            <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-100">Horas de aprendizaje por mes</h3>
            <Bar :data="barData" :options="{ responsive: true, plugins: { legend: { display: false } } }" />
        </div>
        <div>
            <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-100">Top ejercicios más intentados</h3>
            <Bar :data="topData" :options="{ responsive: true, plugins: { legend: { display: false } } }" />
        </div>
    </div>
</template>
