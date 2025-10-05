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
import ChooseAudioFields from './components/ChooseAudioFields.vue';
import ListenAndAnswerFields from './components/ListenAndAnswerFields.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
        case 'Elige lo que escuchas':
            return ChooseAudioFields;
        case 'Escucha y responde':
            return ListenAndAnswerFields;
        case 'Respuesta corta':
        case 'Ensayo':
            return ShortAnswerFields;
        default:
            return null;
    }
});
</script>

<template>
    <div class="space-y-8">
        <!-- Campos básicos -->
        <div class="space-y-6">
            <!-- Enunciado (toda la línea) -->
            <div>
                <div class="flex items-center gap-2 text-gray-300">
                    <i class="fa-solid fa-file-lines text-gray-400"></i>
                    <InputLabel for="prompt" value="Enunciado" />
                </div>
                <TextInput id="prompt" v-model="form.prompt" type="text" class="mt-1 block w-full" />
                <InputError :message="errors.prompt" class="mt-2" />
            </div>

            <!-- Lección y Tipo en la misma línea -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center gap-2 text-gray-300">
                        <i class="fa-solid fa-book-open text-gray-400"></i>
                        <InputLabel for="lesson_id" value="Lección" />
                    </div>
                    <select id="lesson_id" v-model="form.lesson_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Selecciona una lección</option>
                        <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">{{ lesson.name }}</option>
                    </select>
                    <InputError :message="errors.lesson_id" class="mt-2" />
                </div>

                <div>
                    <div class="flex items-center gap-2 text-gray-300">
                        <i class="fa-solid fa-list-check text-gray-400"></i>
                        <InputLabel for="exercise_type_id" value="Tipo de Ejercicio" />
                    </div>
                    <select id="exercise_type_id" v-model="form.exercise_type_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Selecciona un tipo</option>
                        <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                    <InputError :message="errors.exercise_type_id" class="mt-2" />
                </div>
            </div>

            <!-- Explicación (toda la línea) -->
            <div>
                <div class="flex items-center gap-2 text-gray-300">
                    <i class="fa-solid fa-info-circle text-gray-400"></i>
                    <InputLabel for="explanation" value="Explicación (opcional)" />
                </div>
                <TextArea id="explanation" v-model="form.explanation" rows="2" class="mt-1 block w-full" />
                <InputError :message="errors.explanation" class="mt-2" />
            </div>
        </div>

        <!-- Campos dinámicos por tipo -->
        <div v-if="componentMap"
            class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-gray-400"></i>
                Configuración específica
            </h3>
            <component :is="componentMap" :form="form" :errors="errors" />
        </div>

        <!-- Acciones -->
        <div class="mt-8 flex justify-end gap-2 pt-2">
            <PrimaryButton type="submit" :disabled="form.processing"
                class="bg-indigo-600 hover:bg-indigo-800 text-white flex items-center gap-2">
                <i class="fa-solid fa-save"></i>
                {{ isEdit ? 'Actualizar' : 'Guardar' }}
            </PrimaryButton>
        </div>
    </div>
</template>
