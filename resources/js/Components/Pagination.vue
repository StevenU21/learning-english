<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
    meta: { type: Object, default: null }, // { from, to, total }
});

const prevLink = computed(() => (props.links && props.links.length ? props.links[0] : null));
const nextLink = computed(() => (props.links && props.links.length ? props.links[props.links.length - 1] : null));
</script>

<template>
    <div v-if="links && links.length > 0" class="px-4 py-3 sm:px-6">
        <div class="flex items-center justify-between">
            <!-- Mobile: only previous/next -->
            <div class="flex justify-between flex-1 sm:hidden">
                <span v-if="!prevLink?.url"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:text-gray-600 dark:bg-gray-800 dark:border-gray-600">
                    Anterior
                </span>
                <Link v-else :href="prevLink.url" preserve-scroll preserve-state
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 transition dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                Anterior
                </Link>

                <span v-if="!nextLink?.url"
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:text-gray-600 dark:bg-gray-800 dark:border-gray-600">
                    Siguiente
                </span>
                <Link v-else :href="nextLink.url" preserve-scroll preserve-state
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 transition dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                Siguiente
                </Link>
            </div>

            <!-- Desktop: results + all controls -->
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div v-if="meta" class="text-sm text-gray-700 dark:text-gray-400">
                    Mostrando
                    <span class="font-medium">{{ meta.from ?? 0 }}</span>
                    a
                    <span class="font-medium">{{ meta.to ?? 0 }}</span>
                    de
                    <span class="font-medium">{{ meta.total ?? 0 }}</span>
                    resultados
                </div>
                <div v-else></div>

                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <template v-for="(link, i) in links" :key="i">
                        <!-- Previous -->
                        <span v-if="i === 0 && !link.url"
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white ring-1 ring-inset ring-gray-300 cursor-default rounded-l-md dark:bg-gray-800 dark:ring-gray-600">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                        <Link v-else-if="i === 0" :href="link.url" preserve-scroll preserve-state
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 ring-1 ring-inset ring-gray-300 rounded-l-md transition dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:ring-gray-600">
                        <i class="fa-solid fa-chevron-left"></i>
                        </Link>

                        <!-- Numbers and separators -->
                        <span v-else-if="i !== 0 && i !== links.length - 1 && !link.url"
                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white ring-1 ring-inset ring-gray-300 cursor-default dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-600">
                            <span v-html="link.label" />
                        </span>
                        <Link v-else-if="i !== 0 && i !== links.length - 1" :href="link.url" preserve-scroll
                            preserve-state
                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium ring-1 ring-inset transition"
                            :class="[
                                link.active
                                    ? 'z-10 bg-indigo-600 text-white ring-indigo-600'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 ring-gray-300 dark:ring-gray-600',
                            ]">
                        <span v-html="link.label" />
                        </Link>

                        <!-- Next -->
                        <span v-else-if="i === links.length - 1 && !link.url"
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white ring-1 ring-inset ring-gray-300 cursor-default rounded-r-md dark:bg-gray-800 dark:ring-gray-600">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                        <Link v-else :href="link.url" preserve-scroll preserve-state
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 ring-1 ring-inset ring-gray-300 rounded-r-md transition dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:ring-gray-600">
                        <i class="fa-solid fa-chevron-right"></i>
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
    