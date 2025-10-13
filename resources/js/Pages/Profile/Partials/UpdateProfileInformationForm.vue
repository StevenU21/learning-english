<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FileInput from '@/Components/FileInput.vue';

// This component is for PROFILE data only (avatar, nickname, birthdate, daily_goal_minutes, total_minutes, streak_days, gender)
// User main data (first_name, last_name, email) is handled in UpdateUserForm.vue

type ProfileDto = {
    avatar_url?: string | null;
    nickname?: string | null;
    birthdate?: string | null;
    daily_goal_minutes?: number | null;
    gender?: string | null;
};

const page = usePage();
const profile = computed<Partial<ProfileDto>>(() => (page.props as any).profile ?? {});


const avatarFile = ref<File | null>(null);

const form = useForm<{
    avatar: File | null;
    nickname: string | null;
    birthdate: string | null;
    daily_goal_minutes: number | null;
    gender: string | null;
}>({
    avatar: null,
    nickname: (profile.value?.nickname ?? null) as string | null,
    birthdate: (profile.value?.birthdate ?? null) as string | null,
    daily_goal_minutes: (profile.value?.daily_goal_minutes ?? null) as number | null,
    gender: (profile.value?.gender ?? null) as string | null,
});

watch(() => avatarFile.value, (f) => {
    form.avatar = f ?? null;
});

const submit = () => {
    form.post(route('profile.profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => { },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Datos del perfil
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Actualiza tu foto y datos personales visibles en tu perfil.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="flex items-center gap-4">
                <FileInput id="avatar" name="avatar" accept="image/*" v-model="avatarFile" :class="'w-40'"
                    :preview-url="(profile?.avatar_url as string | null) || '/img/logo03.png'" />
                <div class="flex-1">
                    <InputLabel for="avatar" value="Avatar" />
                    <InputError class="mt-2" :message="form.errors.avatar" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <InputLabel for="nickname" value="Apodo" />
                    <TextInput id="nickname" type="text" class="mt-1 block w-full" v-model="form.nickname" />
                    <InputError class="mt-2" :message="form.errors.nickname" />
                </div>

                <div>
                    <InputLabel for="birthdate" value="Fecha de nacimiento" />
                    <TextInput id="birthdate" type="date" class="mt-1 block w-full" v-model="form.birthdate" />
                    <InputError class="mt-2" :message="form.errors.birthdate" />
                </div>

                <div>
                    <InputLabel for="daily_goal_minutes" value="Meta diaria (minutos)" />
                    <select id="daily_goal_minutes" v-model="form.daily_goal_minutes"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona una opción</option>
                        <option value="5">5 minutos</option>
                        <option value="10">10 minutos</option>
                        <option value="15">15 minutos</option>
                        <option value="20">20 minutos</option>
                        <option value="30">30 minutos</option>
                        <option value="60">60 minutos</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.daily_goal_minutes" />
                </div>

                <div>
                    <InputLabel for="gender" value="Género" />
                    <select id="gender" v-model="form.gender"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona una opción</option>
                        <option value="male">Masculino</option>
                        <option value="female">Femenino</option>

                    </select>
                    <InputError class="mt-2" :message="form.errors.gender" />
                </div>
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
