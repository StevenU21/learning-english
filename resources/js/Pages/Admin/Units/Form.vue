<script setup>
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import FileInput from '@/Components/FileInput.vue';

const props = defineProps({
    form: Object,
    levels: Array,
    submitText: {
        type: String,
        default: 'Guardar'
    },
    onSubmit: Function,
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel for="name" value="Nombre" />
            <TextInput id="name" v-model="form.name" type="text" class="block w-full" autocomplete="name-input" />
            <InputError :message="form.errors.name" class="mt-2" />
        </div>
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <InputLabel for="expected_time" value="Tiempo Esperado" />
                <TextInput id="expected_time" v-model="form.expected_time" type="number" class="block w-full"
                    autocomplete="expected-time-input" />
                <InputError :message="form.errors.expected_time" class="mt-2" />
            </div>
            <div class="flex-1">
                <InputLabel for="level_id" value="Nivel" />
                <SelectInput id="level_id" v-model="form.level_id">
                    <option v-for="level in levels" :value="level.id" :key="level.id">{{ level.name }}</option>
                </SelectInput>
                <InputError :message="form.errors.level_id" class="mt-2" />
            </div>
        </div>
        <div>
            <InputLabel for="image" value="Imagen" />
            <FileInput id="image" v-model="form.image" accept="image/*" />
            <InputError :message="form.errors.image" class="mt-2" />
        </div>
        <div>
            <InputLabel for="description" value="Descripción" />
            <TextArea id="description" v-model="form.description" class="mt-1 block w-full"
                autocomplete="description-input" />
            <InputError :message="form.errors.description" class="mt-2" />
        </div>
        <div class="mt-8 flex justify-end">
            <PrimaryButton type="submit" :disabled="form.processing">
                <i class="fas fa-save mr-2"></i> {{ submitText }}
            </PrimaryButton>
        </div>
    </div>
</template>
