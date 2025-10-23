<script setup>
import Pagination from '@/Components/Pagination.vue';
import { computed } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true }, // [{ label, key, class, render? }]
    items: { type: Array, required: true },
    meta: { type: Object, default: null },
    links: { type: Array, default: null },
    loading: { type: Boolean, default: false },
    emptyText: { type: String, default: 'Sin registros' },
});

const emit = defineEmits(['paginate']);

function handlePagination(e) {
    // Si el evento es un click en un link, pasar el href directamente
    let href = null;
    if (typeof e === 'string') {
        href = e;
    } else if (e?.target?.href) {
        href = e.target.href;
    } else if (e?.target?.closest) {
        href = e.target.closest('a')?.href;
    }
    if (href) emit('paginate', href);
}
</script>

<template>
    <div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-max text-sm">
                <thead class="bg-gray-100 dark:bg-gray-900/70">
                    <tr>
                        <th v-for="col in columns" :key="col.key" :class="col.class || 'px-4 py-3 text-left text-gray-700 dark:text-gray-300'">
                            <span v-if="col.icon" :class="col.icon + ' mr-2'" />{{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td :colspan="columns.length" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Cargando...</td>
                    </tr>
                    <tr v-else-if="items.length === 0">
                        <td :colspan="columns.length" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">{{ emptyText }}</td>
                    </tr>
                    <tr v-else v-for="item in items" :key="item.id || item.exercise?.id">
                        <td v-for="col in columns" :key="col.key" :class="col.class || 'px-4 py-2 text-gray-800 dark:text-gray-200'">
                            <slot :name="col.key" :item="item">
                                <span v-if="col.render">{{ col.render(item) }}</span>
                                <span v-else>{{ item[col.key] }}</span>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <Pagination v-if="links && links.length > 0" :links="links" :meta="meta" @click="handlePagination" />
    </div>
</template>
