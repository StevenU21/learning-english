<script setup>
import { computed } from 'vue';
import { usePage, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const page = usePage();
const user = computed(() => page.props.user ?? page.props.auth?.user ?? {});
const profile = computed(() => page.props.profile ?? user.value.profile ?? null);
const stats = computed(() => page.props.stats ?? {});

// Roles y layout (igual que en Edit.vue): admin -> AuthenticatedLayout, otros -> StudentLayout
const roles = computed(() => page.props.auth.user?.roles || []);
const isAdmin = computed(() => roles.value.includes('admin'));
const Layout = computed(() => (isAdmin.value ? AuthenticatedLayout : StudentLayout));

// Map roles to Spanish labels
const roleLabels = { student: 'Estudiante', admin: 'Administrador' };
const displayRoles = computed(() => Array.isArray(roles.value) ? roles.value.map(r => roleLabels[r] ?? r) : []);

// Helpers for formatting and labels
const formatDate = (d) => {
    if (!d) return null;
    const date = new Date(d);
    if (Number.isNaN(date.getTime())) return d;
    return new Intl.DateTimeFormat('es-ES', { year: 'numeric', month: 'long', day: '2-digit' }).format(date);
};
const birthdateFormatted = computed(() => formatDate(profile.value?.birthdate));
const genderLabels = { male: 'Masculino', female: 'Femenino' };
const genderLabel = computed(() => {
    const g = profile.value?.gender;
    if (!g) return null;
    const key = String(g).toLowerCase();
    return genderLabels[key] ?? g;
});
</script>

<template>
    <component :is="Layout">

        <Head title="Perfil" />

        <template #header>
            <PageHeader title="Perfil" subtitle="Resumen de tu cuenta" icon="fa-solid fa-user" :breadcrumbs="[
                { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                { label: 'Perfil' }
            ]" gradient-classes="from-purple-600 to-indigo-600">
                <template #actions>
                    <Link :href="route('profile.edit')"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Editar perfil
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                            <img :src="profile?.avatar_url || '/img/logo03.png'" alt="Avatar"
                                class="h-28 w-28 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 mx-auto sm:mx-0" />

                            <div class="flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-2 items-center justify-center text-center sm:justify-start sm:text-left">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ user.full_name }}
                                    </h3>
                                    <div class="flex gap-2">
                                        <span v-for="r in displayRoles" :key="r"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:ring-indigo-700/50">
                                            <i class="fa-solid fa-user-shield" v-if="r === 'Administrador'"></i>
                                            <i class="fa-solid fa-graduation-cap" v-else></i>
                                            {{ r }}
                                        </span>
                                    </div>
                                    <hr class="my-8 border-t border-gray-300 dark:border-gray-700" />
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div
                                        class="col-span-2 flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:shadow-none transition-colors">
                                        <i class="fa-solid fa-envelope mt-1 text-gray-400"></i>
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Correo
                                            </div>
                                            <div class="text-sm">{{ user.email }}</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:shadow-none transition-colors">
                                        <i class="fa-solid fa-signature mt-1 text-gray-400"></i>
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Apodo
                                            </div>
                                            <div class="text-sm">{{ profile?.nickname || '-' }}</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:shadow-none transition-colors">
                                        <i class="fa-solid fa-cake-candles mt-1 text-gray-400"></i>
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Nacimiento
                                            </div>
                                            <div class="text-sm">{{ birthdateFormatted || '-' }}</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:shadow-none transition-colors">
                                        <i class="fa-solid fa-venus-mars mt-1 text-gray-400"></i>
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Género
                                            </div>
                                            <div class="text-sm">{{ genderLabel || '-' }}</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:shadow-none transition-colors">
                                        <i class="fa-solid fa-clock mt-1 text-gray-400"></i>
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Minutos totales
                                            </div>
                                            <div class="text-sm">{{ (profile?.total_minutes != null) ?
                                                profile.total_minutes :
                                                '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats overview cards -->
                        <div class="mt-8 grid grid-cols-2 gap-4 xl:grid-cols-5">
                            <!-- Daily goal progress -->
                            <div v-if="(profile?.daily_goal_minutes ?? 0) > 0"
                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Meta diaria</div>
                                    <i class="fa-solid fa-flag-checkered text-indigo-500"></i>
                                </div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Hoy: <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                                        stats.daily?.today ?? 0
                                        }}</span> / {{ stats.daily?.goal ?? 0 }} min
                                </div>
                                <div class="mt-2 h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded bg-indigo-600"
                                        :style="{ width: Math.min(100, Math.max(0, ((stats.daily?.today ?? 0) / Math.max(1, stats.daily?.goal ?? 1)) * 100)) + '%' }">
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <template v-if="!(stats.daily?.reached)">
                                        Te faltan <span class="font-semibold">{{ stats.daily?.remaining ?? 0 }}</span>
                                        min para tu meta de hoy.
                                    </template>
                                    <template v-else>
                                        ¡Meta alcanzada hoy!
                                    </template>
                                </div>
                            </div>
                            <!-- Overall progress -->
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Progreso general
                                    </div>
                                    <i class="fa-solid fa-chart-line text-indigo-500"></i>
                                </div>
                                <div class="mt-2 text-2xl font-semibold">{{ (stats.overall?.progress ?? 0).toFixed(1)
                                }}%</div>
                                <div class="mt-2 h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded bg-indigo-600"
                                        :style="{ width: Math.min(100, Math.max(0, stats.overall?.progress ?? 0)) + '%' }">
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Última actividad: {{ stats.overall?.last_activity || '—' }}
                                </div>
                            </div>

                            <!-- Units -->
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Unidades</div>
                                    <i class="fa-solid fa-layer-group text-emerald-500"></i>
                                </div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Trabajadas: <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                                        stats.units?.worked ?? 0 }}</span> / {{ stats.units?.total ?? 0 }} ·
                                    Completadas: <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                                        stats.units?.completed ?? 0
                                    }}</span>
                                </div>
                                <div class="mt-2 h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded bg-emerald-500"
                                        :style="{ width: Math.min(100, Math.max(0, stats.units?.avg_progress ?? 0)) + '%' }">
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Promedio de avance: {{
                                    (stats.units?.avg_progress ?? 0).toFixed(1) }}%</div>
                            </div>

                            <!-- Lessons -->
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Lecciones</div>
                                    <i class="fa-solid fa-book-open text-amber-500"></i>
                                </div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Trabajadas: <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                                        stats.lessons?.worked ?? 0 }}</span> / {{ stats.lessons?.total ?? 0 }} ·
                                    Completadas:
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{
                                        stats.lessons?.completed ??
                                        0 }}</span>
                                </div>
                                <div class="mt-2 h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded bg-amber-500"
                                        :style="{ width: Math.min(100, Math.max(0, stats.lessons?.avg_progress ?? 0)) + '%' }">
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Promedio de avance: {{
                                    (stats.lessons?.avg_progress ?? 0).toFixed(1) }}%</div>
                            </div>

                            <!-- Exercises accuracy -->
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Precisión
                                        ejercicios</div>
                                    <i class="fa-solid fa-bullseye text-pink-500"></i>
                                </div>
                                <div class="mt-2 text-2xl font-semibold">{{ (stats.exercises?.accuracy ?? 0).toFixed(1)
                                }}%
                                </div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Correctas: <span
                                        class="font-semibold text-gray-800 dark:text-gray-200">{{
                                            stats.exercises?.correct ?? 0
                                        }}</span> / {{ stats.exercises?.attempts ?? 0 }}</div>
                                <div class="mt-2 h-2 w-full rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded bg-pink-500"
                                        :style="{ width: Math.min(100, Math.max(0, stats.exercises?.accuracy ?? 0)) + '%' }">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick actions -->
                        <div class="mt-6 flex flex-wrap gap-3 justify-center sm:justify-start">
                            <Link v-if="!isAdmin" :href="route('student.units.index')"
                                class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fa-solid fa-arrow-right"></i>
                            Continuar aprendiendo
                            </Link>
                            <Link v-else :href="route('admin.progress.index')"
                                class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300">
                            <i class="fa-solid fa-chart-column"></i>
                            Ver progreso de estudiantes
                            </Link>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
