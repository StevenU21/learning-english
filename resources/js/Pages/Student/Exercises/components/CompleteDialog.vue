<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({ exercise: Object, showFeedback: Boolean, unitSlug: { type: [String, Number], required: false } });
const emit = defineEmits(['answered']);

const selected = ref([]);
const showConfirm = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const numberedOptions = ref([]); // [{text, num}]

function toggle(option) {
    if (props.showFeedback) return;
    if (selected.value.includes(option)) {
        selected.value = [];
    } else {
        selected.value = [option];
    }
}

function submit() {
    if (props.showFeedback) return;
    const target = Array.isArray(props.exercise.solution) ? props.exercise.solution : [];
    const correct = JSON.stringify([...selected.value].sort()) === JSON.stringify([...target].sort());
    emit('answered', correct, selected.value);
}

function getButtonClass(opt) {
    if (props.showFeedback && selected.value.includes(opt)) {
        const isCorrect = Array.isArray(props.exercise.solution) ? props.exercise.solution.includes(opt) : false;
        return isCorrect
            ? 'bg-gray-800 border-green-500 text-green-400'
            : 'bg-gray-800 border-red-500 text-red-400';
    }
    if (selected.value.includes(opt)) {
        return 'bg-gray-800 border-blue-400 text-blue-400';
    }
    return 'bg-gray-800/80 border-blue-400/30 text-white';
}

function updateWindowWidth() {
    windowWidth.value = window.innerWidth;
}

function initializeOptions() {
    numberedOptions.value = (props.exercise.options || []).map((opt, idx) => ({ text: opt, num: idx + 1 }));
}
initializeOptions();

function handleFinish() {
    showConfirm.value = true;
}

function confirmFinish() {
    showConfirm.value = false;
    router.get(route('student.units.lessons.index', { unit: props.unitSlug }));
}

function handleKeydown(e) {
    if (props.showFeedback || showConfirm.value) return;
    if (window.innerWidth < 768) return;
    if (/^[1-9]$/.test(e.key)) {
        const num = parseInt(e.key, 10);
        const optObj = numberedOptions.value.find(o => o.num === num);
        if (optObj) toggle(optObj.text);
        return;
    }
    if (e.key === 'Enter' && selected.value.length > 0) {
        submit();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', updateWindowWidth);
    updateWindowWidth();
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('resize', updateWindowWidth);
});
</script>
<template>
    <div class="flex flex-col gap-6 w-full">
        <div class="flex flex-col gap-2 w-full">
            <button v-for="(optObj, idx) in numberedOptions" :key="optObj.text" @click="toggle(optObj.text)"
                :disabled="showFeedback" :class="[
                    'w-full text-center px-4 py-3 rounded-xl border-2 text-lg font-bold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-70',
                    getButtonClass(optObj.text)
                ]" style="display: flex; align-items: center;">
                <span v-if="windowWidth >= 768"
                    class="inline-block px-2 py-1 rounded border border-blue-400/30 text-base font-semibold text-gray-300 bg-transparent select-none"
                    style="min-width:2.2rem;text-align:center; margin-right:0.75rem;">
                    {{ optObj.num }}
                </span>
                <span style="flex:1; text-align:center;">{{ optObj.text }}</span>
            </button>
        </div>
        <div class="flex justify-between mt-4 w-full">
            <DangerButton @click="handleFinish" class="hidden md:flex w-1/3 items-center">
                <i class="fa-solid fa-flag-checkered mr-2"></i>
                <span class="flex-1 text-center">Terminar</span>
            </DangerButton>
            <div class="flex-1"></div>
            <PrimaryButton @click="submit" :disabled="showFeedback || selected.length === 0" :class="[
                'w-full md:w-1/3 flex items-center',
                (showFeedback || selected.length === 0) ? 'opacity-60 cursor-not-allowed' : ''
            ]">
                <span v-if="windowWidth >= 768"
                    class="inline-flex items-center justify-center px-2 py-1 rounded border border-blue-400/60 text-base font-bold text-gray-800 bg-gray-100 select-none"
                    style="min-width:2.2rem; height:2.2rem; margin-right:0.75rem;">
                    ⏎
                </span>
                <span v-else class="inline-flex items-center">
                    <i class="fa-solid fa-check mr-2"></i>
                    Comprobar
                </span>
                <span v-if="windowWidth >= 768" class="flex-1 text-center">Comprobar</span>
            </PrimaryButton>
        </div>
        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-[#18232a] rounded-lg shadow-lg p-6 max-w-sm w-full border border-gray-700">
                <h2 class="text-lg font-semibold mb-2 text-white">¿Salir de la lección?</h2>
                <p class="mb-4 text-gray-300">Si sales ahora, tu avance no se guardará. ¿Estás seguro que quieres salir?
                </p>
                <div class="flex gap-3 justify-center">
                    <SecondaryButton @click="showConfirm = false" class="flex items-center px-4 py-2">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        Cancelar
                    </SecondaryButton>
                    <DangerButton @click="confirmFinish" class="flex items-center px-4 py-2">
                        <i class="fa-solid fa-flag-checkered mr-2"></i>
                        Salir sin guardar
                    </DangerButton>
                </div>
            </div>
        </div>
    </div>
</template>
