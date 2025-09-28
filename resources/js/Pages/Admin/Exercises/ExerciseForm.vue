<script setup>
import { computed, watch } from 'vue';
import MultipleChoiceFields from './components/MultipleChoiceFields.vue';
import TrueFalseFields from './components/TrueFalseFields.vue';
import ShortAnswerFields from './components/ShortAnswerFields.vue';
import MatchColumnsFields from './components/MatchColumnsFields.vue';
import OrderElementsFields from './components/OrderElementsFields.vue';
import CompleteSpacesFields from './components/CompleteSpacesFields.vue';
import MatchDefinitionsFields from './components/MatchDefinitionsFields.vue';
import CompleteDialogFields from './components/CompleteDialogFields.vue';

const props = defineProps({
    form: { type: Object, required: true },
    types: { type: Array, required: true },
    lessons: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
    onSubmit: { type: Function, required: true },
    isEdit: { type: Boolean, default: false }
});

const selectedType = computed(() => props.types.find(t => t.id === props.form.exercise_type_id) || {});

watch(() => props.form.exercise_type_id, () => {
    props.form.options = [];
    props.form.solution = [];
});

const componentMap = computed(() => {
    switch (selectedType.value.name) {
        case 'Opción múltiple':
            return MultipleChoiceFields;
        case 'Verdadero o falso':
            return TrueFalseFields;
        case 'Completar espacios':
            return CompleteSpacesFields;
        case 'Relacionar columnas':
            return MatchColumnsFields;
        case 'Ordenar elementos':
            return OrderElementsFields;
        case 'Emparejar definiciones':
            return MatchDefinitionsFields;
        case 'Completar diálogo':
            return CompleteDialogFields;
        case 'Respuesta corta':
        case 'Ensayo':
            return ShortAnswerFields;
        default:
            return null;
    }
});
</script>

<template>
    <form @submit.prevent="onSubmit" class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="flex flex-col space-y-1">
                <label class="text-sm font-medium">Enunciado</label>
                <input v-model="form.prompt" type="text" class="input input-bordered w-full" />
                <p v-if="errors.prompt" class="text-xs text-red-500">{{ errors.prompt }}</p>
            </div>

            <div class="flex flex-col space-y-1">
                <label class="text-sm font-medium">Lección</label>
                <select v-model="form.lesson_id" class="input input-bordered w-full">
                    <option value="">Selecciona una lección</option>
                    <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">{{ lesson.name }}</option>
                </select>
                <p v-if="errors.lesson_id" class="text-xs text-red-500">{{ errors.lesson_id }}</p>
            </div>

            <div class="flex flex-col space-y-1">
                <label class="text-sm font-medium">Explicación (opcional)</label>
                <input v-model="form.explanation" type="text" class="input input-bordered w-full" />
                <p v-if="errors.explanation" class="text-xs text-red-500">{{ errors.explanation }}</p>
            </div>

            <div class="flex flex-col space-y-1">
                <label class="text-sm font-medium">Tipo de Ejercicio</label>
                <select v-model="form.exercise_type_id" class="input input-bordered w-full">
                    <option value="">Selecciona un tipo</option>
                    <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
                <p v-if="errors.exercise_type_id" class="text-xs text-red-500">{{ errors.exercise_type_id }}</p>
            </div>
        </div>

        <div v-if="componentMap" class="border rounded p-4 bg-gray-50 dark:bg-gray-700/30">
            <component :is="componentMap" :form="form" :errors="errors" />
        </div>

        <div class="pt-4">
            <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow">
                {{ isEdit ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
