<template>
    <div>
        <input v-bind="$attrs" type="file"
            class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded focus:border-indigo-500 focus:ring-indigo-500"
            @change="e => emit('update:modelValue', (e.target as HTMLInputElement)?.files?.[0] || null)" />
        <div v-if="previewUrl" class="mt-2">
            <img :src="previewUrl" alt="Vista previa" class="max-h-40 rounded shadow" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{ modelValue?: File | null }>();
const emit = defineEmits(['update:modelValue']);

const previewUrl = ref<string | null>(null);

watch(
    () => props.modelValue,
    (file) => {
        if (file && file.type && file.type.startsWith('image/')) {
            previewUrl.value = URL.createObjectURL(file);
        } else {
            previewUrl.value = null;
        }
    },
    { immediate: true }
);
</script>
