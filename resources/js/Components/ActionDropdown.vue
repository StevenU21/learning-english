<template>
    <div ref="rootEl" class="relative inline-block text-left">
        <PrimaryButton @click="toggleDropdown">
            <span class="mr-2">{{ isOpen ? 'Cerrar' : 'Mostrar' }}</span>
            <i :class="isOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'"></i>
        </PrimaryButton>
        <teleport to="body">
            <transition name="dropdown">
                <div v-if="isOpen" ref="menuEl"
                    class="fixed w-auto sm:w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded shadow-lg z-50"
                    :style="menuStyle">
                    <div class="dropdown-content p-2">
                        <slot />
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import eventBus from '@/Components/eventBus.js';

// State refs
const isOpen = ref(false);
const rootEl = ref(null);
const menuEl = ref(null);
const menuStyle = ref({ top: '0px', left: '0px', transform: 'translateX(-100%)', marginTop: '8px' });

const toggleDropdown = (event) => {
    event.stopPropagation();
    if (!isOpen.value) {
        eventBus.emit('closeAll');
    }
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        nextTick(updatePosition);
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    const root = rootEl.value;
    const menu = menuEl.value;
    if (!root) return;
    const clickedInsideRoot = root.contains(event.target);
    const clickedInsideMenu = menu && menu.contains(event.target);
    if (!clickedInsideRoot && !clickedInsideMenu) {
        closeDropdown();
    }
};

/**
 * Position menu below the PrimaryButton within rootEl
 */
const updatePosition = () => {
    const root = rootEl.value;
    if (!root) return;
    // Find actual button DOM element inside PrimaryButton
    const btn = root.querySelector('button');
    if (!btn) return;
    const rect = btn.getBoundingClientRect();
    menuStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${rect.right}px`,
        transform: 'translateX(-100%)',
    };
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    eventBus.on('closeAll', closeDropdown);
    window.addEventListener('scroll', updatePosition, true);
    window.addEventListener('resize', updatePosition);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    eventBus.off('closeAll', closeDropdown);
    window.removeEventListener('scroll', updatePosition, true);
    window.removeEventListener('resize', updatePosition);
});
</script>

<style scoped>
.z-50 {
    z-index: 50;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.dropdown-content>*:not(:last-child) {
    margin-bottom: 0.25rem;
}
</style>
