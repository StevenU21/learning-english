<script setup>
import { defineProps } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    form: Object,
    units: Array,
    submitText: {
        type: String,
        default: 'Guardar'
    },
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <InputLabel for="name" value="Nombre" />
            <TextInput id="name" v-model="form.name" type="text" class="block w-full" autocomplete="name-input" />
            <InputError :message="form.errors.name" class="mt-2" />
        </div>
        <div>
            <InputLabel for="unit_id" value="Unidad" />
            <SelectInput id="unit_id" v-model="form.unit_id">
                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>
            </SelectInput>
            <InputError :message="form.errors.unit_id" class="mt-2" />
        </div>
        <div>
            <InputLabel for="description" value="Descripción" />
            <TextArea id="description" v-model="form.description" class="mt-1 block w-full" autocomplete="description-input" />
            <InputError :message="form.errors.description" class="mt-2" />
        </div>
        <div class="mt-8 flex justify-end">
            <PrimaryButton type="submit" :disabled="form.processing">
                <i class="fas fa-save mr-2"></i> {{ submitText }}
            </PrimaryButton>
        </div>
    </div>
</template>
