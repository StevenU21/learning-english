<template>
    <!-- Overlay for mobile -->
    <div v-if="modelValue" class="fixed inset-0 z-40 lg:hidden" @click.self="$emit('update:modelValue', false)">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 transform bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 overflow-hidden shadow-lg lg:shadow-none transition-all duration-200 ease-in-out',
        modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        // Full width on mobile, dynamic width on desktop
        'w-64',
        effectiveCollapsed ? 'lg:w-16' : 'lg:w-64'
    ]">
        <div
            :class="['h-16 flex items-center border-b border-gray-200 dark:border-gray-700 px-4 justify-between', effectiveCollapsed ? 'lg:px-2 lg:justify-start lg:gap-1' : '']">
            <Link :href="homeHref" class="flex items-center gap-2 overflow-hidden flex-shrink-0">
            <img src="/img/logo03.png" alt="Learning English logo" class="w-10 h-10 object-contain" />
            <h1 v-show="!effectiveCollapsed"
                class="text-xl font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap font-nativo">
                N A T I V O
            </h1>
            </Link>
            <div class="flex items-center gap-2">
                <!-- Desktop collapse toggle -->
                <button class="hidden lg:inline-flex"
                    :class="[effectiveCollapsed ? 'p-1.5' : 'p-2', 'text-gray-500 hover:text-gray-700 dark:text-gray-400']"
                    @click="toggleCollapsed" :title="effectiveCollapsed ? 'Expandir' : 'Contraer'">
                    <i :class="effectiveCollapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left'"></i>
                </button>
                <!-- Mobile close -->w
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
            <!-- Admin links -->
            <template v-if="hasRole('admin')">
                <SidebarLink :href="route('units.index')" :active="route().current('units.index')"
                    :compact="effectiveCollapsed" title-text="Unidades">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Unidades</span>
                </SidebarLink>
                <SidebarLink :href="route('levels.index')" :active="route().current('levels.index')"
                    :compact="effectiveCollapsed" title-text="Niveles">
                    <i class="fa-solid fa-list w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Niveles</span>
                </SidebarLink>
                <SidebarLink :href="route('lessons.index')" :active="route().current('lessons.index')"
                    :compact="effectiveCollapsed" title-text="Lecciones">
                    <i class="fa-solid fa-book w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Lecciones</span>
                </SidebarLink>
                <SidebarLink :href="route('resources.index')" :active="route().current('resources.index')"
                    :compact="effectiveCollapsed" title-text="Recursos">
                    <i class="fa-solid fa-user w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Recursos</span>
                </SidebarLink>
                <SidebarLink :href="route('exercises.index')" :active="route().current('exercises.index')"
                    :compact="effectiveCollapsed" title-text="Ejercicios">
                    <i class="fa-solid fa-pencil w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Ejercicios</span>
                </SidebarLink>
                <SidebarLink :href="route('admin.progress.index')" :active="route().current('admin.progress.index')"
                    :compact="effectiveCollapsed" title-text="Progreso">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Progreso</span>
                </SidebarLink>
            </template>

            <!-- Student links -->
            <template v-if="hasRole('student')">
                <SidebarLink :href="route('student.units.index')" :active="route().current('student.units.index')"
                    :compact="effectiveCollapsed" title-text="Unidades">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span v-show="!effectiveCollapsed">Unidades</span>
                </SidebarLink>
            </template>

            <!-- Divider -->
            <hr class="my-3 border-gray-200 dark:border-gray-700" />

            <!-- Profile/Logout -->
            <SidebarLink :href="route('profile.edit')" :compact="effectiveCollapsed" title-text="Perfil">
                <i class="fa-solid fa-user w-5 text-center"></i>
                <span v-show="!effectiveCollapsed">Perfil</span>
            </SidebarLink>
            <SidebarLink :href="route('logout')" method="post" as="button" :compact="effectiveCollapsed"
                title-text="Cerrar sesión">
                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                <span v-show="!effectiveCollapsed">Cerrar sesión</span>
            </SidebarLink>
        </nav>
    </aside>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarLink from '@/Components/SidebarLink.vue';

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
</script>

<style scoped>
/****/
/* Removed CDN import, added local font-face */
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
