<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// This component handles USER main data: first_name, last_name, email
const page = usePage();
const user = computed(() => page.props.user ?? page.props.auth?.user ?? {});

const form = useForm({
    first_name: user.value.first_name || '',
    last_name: user.value.last_name || '',
    email: user.value.email || '',
});

const submit = () => {
    form.patch(route('profile.user.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Datos de la cuenta</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Nombre, apellidos y correo de inicio de sesión.</p>
        </header>

    <form @submit.prevent="submit" class="mt-6 w-full flex flex-col gap-6">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <InputLabel for="first_name" value="Nombres" />
                    <TextInput id="first_name" type="text" class="mt-1 block w-full" v-model="form.first_name" required
                        autocomplete="given-name" />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div class="flex-1">
                    <InputLabel for="last_name" value="Apellidos" />
                    <TextInput id="last_name" type="text" class="mt-1 block w-full" v-model="form.last_name" required
                        autocomplete="family-name" />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Guardado.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
