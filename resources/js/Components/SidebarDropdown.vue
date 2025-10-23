<template>
    <div class="relative">
        <div :class="[
            'flex items-center cursor-pointer select-none px-3 py-2 rounded transition',
            'hover:bg-gray-100 dark:hover:bg-gray-800',
            'text-gray-800 dark:text-gray-100',
            'text-[15px] font-normal',
            { 'justify-center px-0': compact },
            { 'bg-gray-100 dark:bg-gray-800': open }
        ]" @click="toggle" :title="label">
            <i :class="[icon, 'w-5 text-center', 'text-gray-500 dark:text-gray-200']"></i>
            <span v-show="!compact" class="ml-2 flex-1">{{ label }}</span>
            <i v-show="!compact"
                :class="['fa-solid', open ? 'fa-chevron-up' : 'fa-chevron-down', 'ml-auto text-xs', 'text-gray-500 dark:text-gray-200']"></i>
        </div>
        <transition name="fade">
            <div v-show="open"
                class="absolute left-0 mt-2 min-w-full rounded-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg z-50 overflow-hidden">
                <div class="py-1">
                    <SidebarLink v-for="item in items" :key="item.href" :href="item.href" :active="item.active"
                        :compact="compact" :titleText="item.label">
                        <i :class="[item.icon, 'w-5 text-center', 'text-gray-500 dark:text-gray-200']"></i>
                        <span v-show="!compact" class="text-gray-800 dark:text-gray-100 text-[15px] font-normal">{{ item.label }}</span>
                    </SidebarLink>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import SidebarLink from '@/Components/SidebarLink.vue';

const props = defineProps({
    label: String,
    icon: String,
    items: Array,
    compact: Boolean
});

const open = ref(false);

function toggle() {
    open.value = !open.value;
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
