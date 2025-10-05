<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: '' },
    // breadcrumbs: [{ label: string, href?: string, icon?: string }]
    breadcrumbs: { type: Array, default: () => [] },
    // Tailwind classes to customize the gradient colors
    gradientClasses: { type: String, default: 'from-purple-600 to-indigo-600' },
});
</script>

<template>
    <!-- Breadcrumbs -->
    <nav v-if="breadcrumbs && breadcrumbs.length" class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400"
        aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <template v-for="(bc, idx) in breadcrumbs" :key="idx">
                <li v-if="idx === 0">
                    <component :is="bc.href ? Link : 'span'" :href="bc.href"
                        class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        <i v-if="bc.icon" :class="[bc.icon, 'mr-1']"></i>
                        <span>{{ bc.label }}</span>
                    </component>
                </li>
                <li v-else class="flex items-center">
                    <span class="text-gray-400 mx-1">/</span>
                    <component :is="bc.href ? Link : 'span'" :href="bc.href"
                        :class="bc.href ? 'hover:text-gray-700 dark:hover:text-gray-200 transition-colors' : 'text-gray-700 dark:text-gray-200'">
                        <i v-if="bc.icon" :class="[bc.icon, 'mr-1']"></i>
                        <span>{{ bc.label }}</span>
                    </component>
                </li>
            </template>
        </ol>
    </nav>

    <!-- Page header card -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r shadow-lg animate-gradient"
        :class="gradientClasses">
        <div class="absolute inset-0 opacity-20 pointer-events-none"
            style="background-image: radial-gradient(ellipse at top left, rgba(255,255,255,.35), transparent 40%), radial-gradient(ellipse at bottom right, rgba(0,0,0,.25), transparent 40%);">
        </div>
        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center">
                        <i v-if="icon" :class="[icon, 'text-white/90 mr-3']"></i>
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="mt-1 text-white/80 text-sm">{{ subtitle }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <slot name="actions" />
                </div>
            </div>
                <!-- Optional filters area -->
                <div v-if="$slots.filters" class="mt-4">
                    <slot name="filters" />
                </div>
        </div>
    </section>
</template>

<style scoped>
.animate-gradient {
    background-image: linear-gradient(90deg, #c026d3, #7c3aed, #4f46e5, #c026d3);
    background-size: 300% 100%;
    animation: gradientShift 8s linear infinite alternate;
    filter: saturate(1.2) contrast(1.05);
    will-change: background-position;
}

@keyframes gradientShift {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .animate-gradient {
        animation: none;
    }
}
</style>
