<script setup>
        import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
        import { Head, Link } from '@inertiajs/vue3';
        import PrimaryButton from '@/Components/PrimaryButton.vue';
        import StatusBadge from '@/Components/StatusBadge.vue';
        import PageHeader from '@/Components/PageHeader.vue';
        import { ref, computed } from 'vue';
        import { usePage } from '@inertiajs/vue3';
        import ExerciseAttemptsModal from './ExerciseAttemptsModal.vue';

        const props = defineProps({
            user: Object,
            units: Array,
            lessons: Array,
            lessonProgress: Array,
            unitProgress: Array,
            attempts: Array,
        });

        const { user, lessonProgress, unitProgress, attempts, units, lessons } = props;
        const page = usePage();

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

        // Lista plana filtrada
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

        // Agrupado por ejercicio (para mostrar cantidad de intentos por ejercicio)
        const groupedByExercise = computed(() => {
            const map = new Map();
            for (const att of filteredAttempts.value) {
                const ex = att.exercise;
                if (!ex) continue;
                if (!map.has(ex.id)) {
                    map.set(ex.id, { exercise: ex, lesson: att.lesson || ex.lesson, attempts: [] });
                }
                map.get(ex.id).attempts.push(att);
            }
            // Aplanar y calcular el último intento por ejercicio
            return Array.from(map.values()).map(g => {
                const latest = g.attempts.reduce((prev, curr) => {
                    // Priorizar por attempt_number, fallback a answered_at
                    if (!prev) return curr;
                    if ((curr.attempt_number ?? 0) !== (prev.attempt_number ?? 0)) {
                        return (curr.attempt_number ?? 0) > (prev.attempt_number ?? 0) ? curr : prev;
                    }
                    const prevTime = prev.answered_at ? new Date(prev.answered_at).getTime() : 0;
                    const currTime = curr.answered_at ? new Date(curr.answered_at).getTime() : 0;
                    return currTime > prevTime ? curr : prev;
                }, null);
                return { ...g, latestAttempt: latest };
            });
        });

        // Modal: Ver detalles de intentos por lección
        const showLessonAttempts = ref(false);
        const selectedLesson = ref(null);
        const selectedLessonAttempts = computed(() => {
            if (!selectedLesson.value) return [];
            const lessonId = selectedLesson.value.id;
            return attempts.filter(a => {
                const lesson = a.lesson || a.exercise?.lesson;
                return String(lesson?.id) === String(lessonId);
            });
        });

        function openLessonAttempts(lesson) {
            selectedLesson.value = lesson;
            showLessonAttempts.value = true;
        }
        function closeLessonAttempts() {
            showLessonAttempts.value = false;
            selectedLesson.value = null;
        }

        // Modal: Ver detalles por ejercicio (intentos y solución)
        const showExerciseAttempts = ref(false);
        const selectedExercise = ref(null);
        const selectedExerciseAttempts = computed(() => {
            if (!selectedExercise.value) return [];
            return groupedByExercise.value.find(g => String(g.exercise.id) === String(selectedExercise.value.id))?.attempts || [];
        });
        function openExerciseAttempts(exercise) {
            selectedExercise.value = exercise;
            showExerciseAttempts.value = true;
        }
        function closeExerciseAttempts() {
            showExerciseAttempts.value = false;
            selectedExercise.value = null;
        }

        </script>

        <template>
            <AuthenticatedLayout>

                <Head :title="`Progreso - ${user.name}`" />
                <template #header>
                    <PageHeader title="Progreso del Estudiante" :subtitle="`Detalle completo del avance de ${user.name}`"
                        icon="fa-solid fa-bars-progress" :breadcrumbs="[
                            { label: 'Inicio', href: '#', icon: 'fa-solid fa-house' },
                            { label: 'Progreso', href: route('admin.progress.index') },
                            { label: 'Detalle' }
                        ]" gradient-classes="from-indigo-600 to-cyan-600">
                        <template #actions>
                            <Link :href="route('admin.progress.index')">
                            <PrimaryButton>
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Volver a la lista
                            </PrimaryButton>
                            </Link>
                        </template>
                    </PageHeader>
                </template>

                <div class="py-0">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
                        <!-- Resumen del usuario -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                                <i class="fa-solid fa-user mr-2"></i>Información del Estudiante
                            </h3>
                            <div class="md:flex md:items-start md:space-x-8">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <img :src="user.avatar_url || '/img/logo03.png'" alt="Avatar"
                                        class="w-24 h-24 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                                </div>
                                <!-- Datos básicos -->
                                <div class="flex-1 space-y-2 text-sm">
                                    <h4 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ user.name }}</h4>
                                    <div class="flex items-center space-x-6 text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center"><i class="fa-solid fa-envelope mr-2"></i>{{ user.email }}</span>
                                        <span class="flex items-center"><i class="fa-solid fa-calendar-days mr-2"></i>{{ new Date(user.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                                <!-- Datos personales detallados -->
                                <div class="w-full md:w-1/3 grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center"><i class="fa-solid fa-user-tag mr-2"></i><span>{{ user.profile?.nickname || 'Sin datos' }}</span></div>
                                    <div class="flex items-center"><i class="fa-solid fa-cake-candles mr-2"></i><span>{{ user.profile?.birthdate ? new Date(user.profile.birthdate).toLocaleDateString() : 'Sin datos' }}</span></div>
                                    <div class="flex items-center"><i class="fa-solid fa-graduation-cap mr-2"></i><span>{{ user.profile?.academic_level || 'Sin datos' }}</span></div>
                                    <div class="flex items-center"><i class="fa-solid fa-venus-mars mr-2"></i><span>{{ user.profile?.gender || 'Sin datos' }}</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Progreso por Unidad -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    <i class="fa-solid fa-layer-group mr-2"></i>Progreso por Unidad
                                </h3>
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
                            <!-- Mobile: cards -->
                            <div class="md:hidden space-y-3">
                                <div v-if="filteredUnitProgress.length === 0"
                                    class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin registros</div>
                                <div v-for="up in filteredUnitProgress" :key="up.id"
                                    class="rounded-md border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">{{ up.unit.name }}</div>
                                            <StatusBadge :status="up.status" />
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">Progreso: {{ up.progress }}%
                                    </div>
                                </div>
                            </div>
                            <!-- Desktop: table -->
                            <div class="overflow-x-auto hidden md:block">
                                <table class="w-full min-w-max text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-900/70">
                                        <tr class="text-left">
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-layer-group mr-2"></i>Unidad</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-bars-progress mr-2"></i>Progreso</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-flag-checkered mr-2"></i>Estado</th>
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
                                                    <StatusBadge :status="up.status" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Progreso por Lección -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    <i class="fa-solid fa-book-open mr-2"></i>Progreso por Lección
                                </h3>
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
                            <!-- Mobile: cards -->
                            <div class="md:hidden space-y-3">
                                <div v-if="filteredLessonProgress.length === 0"
                                    class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin registros</div>
                                <div v-for="lp in filteredLessonProgress" :key="lp.id"
                                    class="rounded-md border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">{{ lp.lesson.name }}</div>
                                            <StatusBadge :status="lp.status" />
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">Unidad: {{ lp.lesson.unit.name }}
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">Progreso: {{ lp.progress }}%
                                    </div>
                                </div>
                            </div>
                            <!-- Desktop: table -->
                            <div class="overflow-x-auto hidden md:block">
                                <table class="w-full min-w-max text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-900/70">
                                        <tr class="text-left">
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-layer-group mr-2"></i>Unidad</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-book-open mr-2"></i>Lección</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-bars-progress mr-2"></i>Progreso</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i
                                                    class="fa-solid fa-flag-checkered mr-2"></i>Estado</th>
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
                                                    <StatusBadge :status="lp.status" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Intentos de Ejercicios -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    <i class="fa-solid fa-pen-to-square mr-2"></i>Intentos de Ejercicios
                                </h3>
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
                            <!-- Mobile: cards -->
                            <div class="md:hidden space-y-3">
                                <div v-if="filteredAttempts.length === 0"
                                    class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin intentos registrados
                                </div>
                                <div v-for="att in filteredAttempts" :key="att.id"
                                    class="rounded-md border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                    <div class="font-medium text-gray-800 dark:text-gray-200 truncate"
                                        :title="att.exercise?.prompt">{{
                                            att.exercise?.prompt?.slice(0, 80) }}<span
                                            v-if="att.exercise?.prompt?.length > 80">…</span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">Lección: {{ att.lesson?.name ||
                                        att.exercise?.lesson?.name }}</div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">Intento: #{{ att.attempt_number
                                        }}</div>
                                    <div class="mt-2 flex items-center justify-between text-sm">
                                        <span
                                            :class="['px-2 py-1 rounded text-xs font-medium', att.is_correct ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300']">{{
                                                att.is_correct ? 'Sí' : 'No' }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ att.answered_at ? new
                                            Date(att.answered_at).toLocaleString() : '-' }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <button
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md shadow"
                                            @click="openLessonAttempts(att.lesson || att.exercise?.lesson)">
                                            <i class="fa-solid fa-eye mr-2"></i>
                                            Ver detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Desktop: table -->
                            <div class="overflow-x-auto hidden md:block">
                                <table class="w-full min-w-max text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-900/70">
                                        <tr class="text-left">
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-file-lines mr-2"></i>Ejercicio</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-book-open mr-2"></i>Lección</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-hashtag mr-2"></i>Intentos</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-check mr-2"></i>Estado actual</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-calendar-check mr-2"></i>Respondido</th>
                                            <th class="px-4 py-3 text-gray-700 dark:text-gray-300"><i class="fa-solid fa-eye mr-2"></i>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="groupedByExercise.length === 0">
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Sin
                                                intentos
                                                registrados</td>
                                        </tr>
                                        <tr v-for="g in groupedByExercise" :key="g.exercise.id"
                                            class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200 truncate max-w-xs"
                                                :title="g.exercise?.prompt">{{ g.exercise?.prompt?.slice(0, 60) }}<span
                                                    v-if="g.exercise?.prompt?.length > 60">...</span></td>
                                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ g.lesson?.name || g.exercise?.lesson?.name }}</td>
                                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ g.attempts.length }}</td>
                                            <td class="px-4 py-2">
                                                <span :class="['px-2 py-1 rounded text-xs font-medium', g.latestAttempt?.is_correct ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300']">
                                                    {{ g.latestAttempt?.is_correct ? 'Sí' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ g.latestAttempt?.answered_at ? new Date(g.latestAttempt.answered_at).toLocaleString() : '-' }}</td>
                                            <td class="px-4 py-2">
                                                <button
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md shadow"
                                                    @click="openExerciseAttempts(g.exercise)">
                                                    <i class="fa-solid fa-eye mr-2"></i> Ver detalles
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <!-- Modal de intentos por ejercicio -->
                        <ExerciseAttemptsModal v-if="showExerciseAttempts" :exercise="selectedExercise" :attempts="selectedExerciseAttempts"
                            @close="closeExerciseAttempts" />
                    </div>
                </div>
            </AuthenticatedLayout>
        </template>
