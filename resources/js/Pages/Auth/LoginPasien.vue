<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Form data untuk login pasien
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Fungsi untuk submit form login
const submit = () => {
    form.post(route('pasien.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <!-- Title untuk halaman -->
        <Head title="Login Pasien" />

        <!-- Header dengan teks sambutan -->
        <div class="text-center text-2xl font-bold mb-6">
            <p>Halo Pasien, Silakan Login</p>
        </div>

        <!-- Form login -->
        <form @submit.prevent="submit">
            <!-- Input untuk email -->
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput 
                    id="email" 
                    type="email" 
                    class="mt-1 block w-full" 
                    v-model="form.email" 
                    required 
                    autofocus 
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Input untuk password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput 
                    id="password" 
                    type="password" 
                    class="mt-1 block w-full" 
                    v-model="form.password" 
                    required 
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Checkbox untuk remember me -->
            <div class="block mt-4">
                <label class="flex items-center">
                    <input name="remember" type="checkbox" v-model="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>


            <!-- Tombol untuk submit form -->
            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :disabled="form.processing" class="ml-4">Log in</PrimaryButton>
            </div>

            <!-- Link untuk pendaftaran -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Belum punya akun? 
                <a 
                    :href="route('register')" 
                    class="text-blue-500 hover:text-blue-700 underline"
                >
                    Daftar disini
                </a>
            </p>
        </div>
        </form>
    </GuestLayout>
</template>
