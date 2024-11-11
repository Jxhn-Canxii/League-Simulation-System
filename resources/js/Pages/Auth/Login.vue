<template>
    <Head title="Log in" />

    <GuestLayout>
        <Link href="/" class="flex items-center justify-center">
            <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
        </Link>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="login">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-3">
                <InputLabel for="password" value="Password" />
                <div class="relative flex w-full">
                    <TextInput
                        id="password"
                        :type="form.show ? 'text' : 'password'"
                        class="block w-full py-2 pr-10 shadow border-3 border-solid border-gray-200 rounded-l"
                        v-model="form.password"
                        required
                        placeholder="Input password"
                        autocomplete="current-password"
                    />
                    <button @click.prevent="togglePasswordVisibility" type="button" class="absolute font-extrabold text-rose-500 inset-y-0 right-0 px-4 rounded-r shadow">
                        <i :class="form.show ? 'fa fa-eye' : 'fa fa-eye-slash'"></i>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-between">
                <label class="inline-flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="mx-2 text-sm text-gray-600">Remember me</span> </label>

                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-600 underline hover:text-gray-900">
                    Forgot your password?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    show: false,
    remember: false
});

const login = () => {
    form.post(route('login'), {
        onFinish: async () => {
            form.reset('password');  // Reset the password field

            // Call seasonsDropdown after login is successful
        },
    });
};



const togglePasswordVisibility = () => {
    form.show = !form.show;
}
</script>
