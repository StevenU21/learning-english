<template>
    <!-- Overlay for mobile -->
    <div v-if="modelValue" class="fixed inset-0 z-40 lg:hidden" @click.self="$emit('update:modelValue', false)">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 transform bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 shadow-lg lg:shadow-none transition-all duration-200 ease-in-out',
        modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        // Full width on mobile, dynamic width on desktop
        'w-64',
        effectiveCollapsed ? 'lg:w-16' : 'lg:w-64'
    ]">
        <div
            :class="['h-16 flex items-center border-b border-gray-200 dark:border-gray-700 px-4 justify-between', effectiveCollapsed ? 'lg:px-2' : '']">
            <Link :href="homeHref" class="flex items-center gap-2 overflow-hidden flex-shrink-0">
            <img src="/img/logo03.png" alt="Learning English logo" class="w-10 h-10 object-contain" />
            <h1 v-show="!effectiveCollapsed"
                class="text-xl font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap font-nativo">
                N A T I V O
            </h1>
            </Link>
            <div class="flex items-center gap-2">
                <!-- Mobile close -->
                <button class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    @click="$emit('update:modelValue', false)">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        <nav :class="[
            effectiveCollapsed ? 'p-2' : 'p-3',
            'space-y-1 overflow-y-auto h-[calc(100vh-4rem)]'
        ]">
            <!-- Gestión -->
            <div :class="[
                'flex items-center gap-2 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 transition-all',
                effectiveCollapsed ? 'justify-center px-0' : ''
            ]">
                <i class="fa-solid fa-briefcase"></i>
                <span v-show="!effectiveCollapsed">Gestión</span>
            </div>
            <hr :class="['my-3 border-gray-200 dark:border-gray-700', effectiveCollapsed ? 'mx-2' : '']" />

            <!-- Admin links -->
            <template v-if="hasRole('admin')">
                <SidebarLink :href="route('units.index')" :active="route().current('units.index')"
                    :compact="effectiveCollapsed" titleText="Unidades">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Unidades</span>
                </SidebarLink>
                <!-- Catálogo Dropdown -->
                <SidebarDropdown :compact="effectiveCollapsed" icon="fa-solid fa-box-archive" label="Catálogo" :items="[
                    {
                        href: route('levels.index'),
                        active: route().current('levels.index'),
                        icon: 'fa-solid fa-list',
                        label: 'Niveles'
                    },
                    {
                        href: route('exercise-types.index'),
                        active: route().current('exercise-types.index'),
                        icon: 'fa-solid fa-shapes',
                        label: 'Tipos de Ejercicio'
                    },
                    {
                        href: route('resources.index'),
                        active: route().current('resources.index'),
                        icon: 'fa-solid fa-user',
                        label: 'Recursos'
                    }
                ]" />

                <SidebarLink :href="route('lessons.index')" :active="route().current('lessons.index')"
                    :compact="effectiveCollapsed" titleText="Lecciones">
                    <i class="fa-solid fa-book w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Lecciones</span>
                </SidebarLink>

                <SidebarLink :href="route('exercises.index')" :active="route().current('exercises.index')"
                    :compact="effectiveCollapsed" titleText="Ejercicios">
                    <i class="fa-solid fa-pencil w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Ejercicios</span>
                </SidebarLink>
                <SidebarLink :href="route('admin.progress.index')" :active="route().current('admin.progress.index')"
                    :compact="effectiveCollapsed" titleText="Progreso">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Progreso</span>
                </SidebarLink>
            </template>

            <!-- Student links -->
            <template v-if="hasRole('student')">
                <SidebarLink :href="route('student.units.index')" :active="route().current('student.units.index')"
                    :compact="effectiveCollapsed" titleText="Unidades">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Unidades</span>
                </SidebarLink>
            </template>

            <!-- Administración -->
            <div :class="[
                'flex items-center gap-2 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 transition-all',
                effectiveCollapsed ? 'justify-center px-0' : ''
            ]">
                <i class="fa-solid fa-gear"></i>
                <span v-show="!effectiveCollapsed">Administración</span>
            </div>
            <hr :class="['my-3 border-gray-200 dark:border-gray-700', effectiveCollapsed ? 'mx-2' : '']" />

            <!-- Profile/Logout -->
            <SidebarLink :href="route('profile.edit')" :compact="effectiveCollapsed" titleText="Perfil">
                <i class="fa-solid fa-user w-5 text-center"></i>
                <span v-show="!effectiveCollapsed">Perfil</span>
            </SidebarLink>
            <SidebarLink :href="route('logout')" method="post" as="button" :compact="effectiveCollapsed"
                titleText="Cerrar sesión">
                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                <span v-show="!effectiveCollapsed">Cerrar sesión</span>
            </SidebarLink>
        </nav>
    </aside>

    <!-- Desktop collapse toggle floating outside so it's always accessible -->
    <button
        class="hidden lg:flex items-center justify-center w-10 h-10 rounded-r-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 fixed top-16 z-50 transform translate-x-1/2 -translate-y-1/2"
        :style="desktopToggleStyle" @click="toggleCollapsed" :title="effectiveCollapsed ? 'Expandir' : 'Contraer'"
        :aria-label="effectiveCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'">
        <i :class="[
            effectiveCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left',
            'text-gray-500 hover:text-gray-700 dark:text-gray-400'
        ]"></i>
    </button>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarLink from '@/Components/SidebarLink.vue';
import SidebarDropdown from '@/Components/SidebarDropdown.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    collapsed: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'update:collapsed']);

const page = usePage();
const roles = computed(() => page.props.auth?.user?.roles || []);
function hasRole(role) {
    return roles.value.includes(role);
}

const homeHref = computed(() => (hasRole('admin') ? route('units.index') : route('student.units.index')));
function toggleCollapsed() {
    emit('update:collapsed', !props.collapsed);
}

// Only collapse on desktop (lg and up). On mobile, always show full labels.
const isDesktop = ref(false);
let mq;
onMounted(() => {
    mq = window.matchMedia('(min-width: 1024px)');
    const set = () => { isDesktop.value = mq.matches; };
    set();
    if (mq.addEventListener) {
        mq.addEventListener('change', set);
    } else if (mq.addListener) {
        // Safari fallback
        mq.addListener(set);
    }
});
onUnmounted(() => {
    const set = () => { };
    if (mq) {
        if (mq.removeEventListener) {
            // We didn't store the original handler reference, but removing isn't critical; just guard.
        } else if (mq.removeListener) {
            // Deprecated API; nothing to remove safely here.
        }
    }
});

const effectiveCollapsed = computed(() => (isDesktop.value ? props.collapsed : false));

// Keep the desktop toggle anchored to the sidebar edge.
const desktopToggleStyle = computed(() => ({
    left: effectiveCollapsed.value ? '4rem' : '16rem',
}));
</script>

<style scoped>
@font-face {
    font-family: 'Suez One';
    font-style: normal;
    font-weight: 400;
    src: url('/fonts/SuezOne-Regular.ttf') format('truetype');
}

.font-nativo {
    font-family: 'Suez One', sans-serif;
    letter-spacing: 0.2em;
}
</style>
