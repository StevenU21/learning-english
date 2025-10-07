<template>
  <!-- Overlay for mobile -->
  <div v-if="modelValue" class="fixed inset-0 z-40 lg:hidden" @click.self="$emit('update:modelValue', false)">
    <div class="absolute inset-0 bg-black/40"></div>
  </div>

  <!-- Sidebar -->
  <aside :class="[
      'fixed inset-y-0 left-0 z-50 transform bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 shadow-lg lg:shadow-none transition-all duration-200 ease-in-out',
      modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      collapsed ? 'w-64 lg:w-14' : 'w-64 lg:w-64'
    ]">
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700">
      <Link :href="homeHref" class="flex items-center gap-2 overflow-hidden">
        <i class="fa-solid fa-layer-group text-xl text-gray-800 dark:text-gray-100"></i>
        <h1 v-show="!collapsed" class="text-xl font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap">
          Learning <span class="text-[#FF2D20]">English</span>
        </h1>
      </Link>
      <div class="flex items-center gap-2">
        <!-- Desktop collapse toggle -->
        <button class="hidden lg:inline-flex p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400" @click="toggleCollapsed" :title="collapsed ? 'Expandir' : 'Contraer'">
          <i :class="collapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left'"></i>
        </button>
        <!-- Mobile close -->
        <button class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400" @click="$emit('update:modelValue', false)">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>

    <nav class="p-3 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
      <!-- Admin links -->
      <template v-if="hasRole('admin')">
        <SidebarLink :href="route('units.index')" :active="route().current('units.index')" :compact="collapsed" title-text="Unidades">
          <i class="fa-solid fa-layer-group w-5 text-center"></i>
          <span v-show="!collapsed">Unidades</span>
        </SidebarLink>
        <SidebarLink :href="route('levels.index')" :active="route().current('levels.index')" :compact="collapsed" title-text="Niveles">
          <i class="fa-solid fa-list w-5 text-center"></i>
          <span v-show="!collapsed">Niveles</span>
        </SidebarLink>
        <SidebarLink :href="route('lessons.index')" :active="route().current('lessons.index')" :compact="collapsed" title-text="Lecciones">
          <i class="fa-solid fa-book w-5 text-center"></i>
          <span v-show="!collapsed">Lecciones</span>
        </SidebarLink>
        <SidebarLink :href="route('resources.index')" :active="route().current('resources.index')" :compact="collapsed" title-text="Recursos">
          <i class="fa-solid fa-user w-5 text-center"></i>
          <span v-show="!collapsed">Recursos</span>
        </SidebarLink>
        <SidebarLink :href="route('exercises.index')" :active="route().current('exercises.index')" :compact="collapsed" title-text="Ejercicios">
          <i class="fa-solid fa-pencil w-5 text-center"></i>
          <span v-show="!collapsed">Ejercicios</span>
        </SidebarLink>
        <SidebarLink :href="route('admin.progress.index')" :active="route().current('admin.progress.index')" :compact="collapsed" title-text="Progreso">
          <i class="fa-solid fa-chart-line w-5 text-center"></i>
          <span v-show="!collapsed">Progreso</span>
        </SidebarLink>
      </template>

      <!-- Student links -->
      <template v-if="hasRole('student')">
        <SidebarLink :href="route('student.units.index')" :active="route().current('student.units.index')" :compact="collapsed" title-text="Unidades">
          <i class="fa-solid fa-layer-group w-5 text-center"></i>
          <span v-show="!collapsed">Unidades</span>
        </SidebarLink>
      </template>

      <!-- Divider -->
      <hr class="my-3 border-gray-200 dark:border-gray-700" />

      <!-- Profile/Logout -->
      <SidebarLink :href="route('profile.edit')" :compact="collapsed" title-text="Perfil">
        <i class="fa-solid fa-user w-5 text-center"></i>
        <span v-show="!collapsed">Perfil</span>
      </SidebarLink>
      <SidebarLink :href="route('logout')" method="post" as="button" :compact="collapsed" title-text="Cerrar sesión">
        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
        <span v-show="!collapsed">Cerrar sesión</span>
      </SidebarLink>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
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
</script>

<style scoped>
/****/
</style>
