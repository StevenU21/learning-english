<script setup>
import { computed } from 'vue';
import ActionDropdown from '@/Components/ActionDropdown.vue';

/**
 * Reusable DataTable component with Tailwind styling.
 * Props:
 * - items: Array of row objects
 * - columns: Array of { key, label, icon?, align?, thClass?, tdClass? }
 * - emptyText: Text for empty state
 * - rowKey: property used as :key for rows (default: 'id')
 * - showActions: whether to render an actions column (default: false)
 * - actionsLabel: header label for actions column (default: 'Acciones')
 * - actionsIcon: icon class for actions header (default: 'fa-solid fa-cogs')
 */
const props = defineProps({
    items: { type: Array, default: () => [] },
    columns: { type: Array, default: () => [] },
    emptyText: { type: String, default: 'No records found.' },
    rowKey: { type: String, default: 'id' },
    showActions: { type: Boolean, default: false },
    actionsLabel: { type: String, default: 'Acciones' },
    actionsIcon: { type: String, default: 'fa-solid fa-cogs' },
    // Pagination props
    links: { type: Array, default: () => [] },
    meta: { type: Object, default: null },
});

const colSpan = computed(() => (props.columns?.length || 0) + (props.showActions ? 1 : 0));

function headerClasses(col) {
    const align = col.align || 'left';
    const base = 'text-gray-800 dark:text-gray-200 p-4';
    const alignClass = align === 'center' ? 'text-center' : align === 'right' ? 'text-right' : 'text-left';
    return [base, alignClass, col.thClass].filter(Boolean).join(' ');
}

function cellClasses(col) {
    const align = col.align || 'left';
    const base = 'px-4 py-2 text-gray-800 dark:text-gray-200';
    const alignClass = align === 'center' ? 'text-center' : align === 'right' ? 'text-right' : 'text-left';
    return [base, alignClass, col.tdClass].filter(Boolean).join(' ');
}

function getValue(row, key) {
    if (!row || !key) return '';
    // Support nested keys like "level.name"
    return key.split('.').reduce((acc, k) => (acc == null ? undefined : acc[k]), row) ?? '';
}
import Pagination from '@/Components/Pagination.vue';
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-200 dark:bg-gray-900">
                <tr>
                    <th v-for="col in columns" :key="col.key" :class="headerClasses(col)">
                        <slot :name="`header-${col.key}`">
                            <i v-if="col.icon" :class="[col.icon, 'mr-2']"></i>
                            {{ col.label }}
                        </slot>
                    </th>
                    <th v-if="showActions" class="text-gray-800 dark:text-gray-200 p-4 text-left">
                        <i :class="[actionsIcon, 'mr-2']"></i>{{ actionsLabel }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!items || items.length === 0">
                    <td :colspan="colSpan" class="text-gray-500 dark:text-gray-400 px-4 py-8 text-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                        {{ emptyText }}
                    </td>
                </tr>
                <tr v-for="(row, rowIndex) in items" :key="row?.[rowKey] ?? rowIndex" class="transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <td v-for="col in columns" :key="col.key" :class="cellClasses(col)">
                        <slot :name="`cell-${col.key}`" :row="row" :index="rowIndex" :value="getValue(row, col.key)">
                            {{ getValue(row, col.key) }}
                        </slot>
                    </td>
                    <td v-if="showActions" class="px-4 py-2 text-center">
                        <div class="hidden md:flex justify-center space-x-2">
                            <slot name="actions" :row="row" :index="rowIndex" />
                        </div>
                        <div class="flex md:hidden justify-center">
                            <ActionDropdown>
                                <slot name="actions" :row="row" :index="rowIndex" />
                            </ActionDropdown>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination section -->
    <div v-if="links && links.length > 0" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">
        <Pagination :links="links" :meta="meta" />
    </div>
    <!-- Usage example:
  <DataTable :items="rows" :columns="cols" show-actions>
    <template #cell-name="{ value }">{{ value.toUpperCase() }}</template>
<template #actions="{ row }">
      <button @click="edit(row)">Edit</button>
    </template>
</DataTable>
-->
</template>

<style scoped>
/* No additional styles; uses Tailwind classes */
</style>
