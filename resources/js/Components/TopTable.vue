<script setup>
const props = defineProps({
    title: { type: String, required: true },
    columns: { type: Array, required: true }, // [{ label, key, align }]
    items: { type: Array, default: () => [] },
    emptyText: { type: String, default: 'Sin registros' },
    totalLabel: { type: String, default: '' },
    totalValue: { type: [String, Number], default: null },
});
</script>

<template>
    <div
        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow flex flex-col">
        <div class="flex items-start justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide">{{ title }}</h3>
        </div>
        <div class="overflow-x-auto -mx-3 flex-1">
            <table class="min-w-full text-xs">
                <thead>
                    <tr
                        class="text-left text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th v-for="col in columns" :key="col.key"
                            :class="['py-2 px-3 font-medium', col.align === 'right' ? 'text-right' : '']">{{ col.label
                            }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-if="items.length === 0">
                        <td :colspan="columns.length" class="py-4 px-3 text-center text-gray-500 dark:text-gray-400">{{
                            emptyText }}</td>
                    </tr>
                    <tr v-for="item in items" :key="item.id || item.name || item.exercise || item.lesson">
                        <td v-for="col in columns" :key="col.key"
                            :class="['py-2 px-3', col.align === 'right' ? 'text-right' : 'text-left', 'text-gray-700 dark:text-gray-300 font-medium']">
                            {{ item[col.key] }}
                        </td>
                    </tr>
                </tbody>
                <tfoot v-if="totalLabel && totalValue !== null">
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td :colspan="columns.length - 1"
                            class="py-2 px-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400">{{
                            totalLabel }}</td>
                        <td class="py-2 px-3 text-right text-[11px] font-semibold text-gray-700 dark:text-gray-200">{{
                            totalValue }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>
