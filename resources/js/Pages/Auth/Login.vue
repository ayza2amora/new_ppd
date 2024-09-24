<script setup>
import { reactive , onMounted} from "vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route("login"), {
        onError: (errors) => {
            if (errors.password) {
                // Custom handling for incorrect password
                form.errors.password = "The password is incorrect.";
            }
        },
    });
};

const imageName = 'dswd-logo-transparent.png';
onMounted(() => {
    sendOtp(); 
});
</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-screen py-2 bg-cover bg-center" style="background-image: url('/ppd-images/bg-image.jpg')">
        <div class="bg-white bg-opacity-75 px-2 pt-2 pb-2 rounded-xl shadow-lg w-full max-w-md mx-2 sm:mx-auto">
            {{ $page.props.flash.success }}
            <div class="flex justify-center mb-1">
                <img
                    :src="`/ppd-images/${imageName}`"
                    alt="DSWD Logo"
                    class="h-25 w-auto"
                />
            </div>
            <div class="bg-transparent w-full rounded px-2 pt-2 pb-2">
                <form @submit.prevent="submit">
                    <div class="mb-2">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            v-model="form.email"
                            type="text"
                            id="username"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-800 focus:outline-none focus:ring focus:ring-blue-400 placeholder-gray-500 placeholder-opacity-50"
                            placeholder="Email"
                        />
                        <!-- Display email error -->
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            id="password"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-800 focus:outline-none focus:ring focus:ring-blue-400 placeholder-gray-500 placeholder-opacity-50"
                            placeholder="Password"
                        />
                        <!-- Display password error -->
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>
                  
                    <div class="flex items-center justify-center">
                        <button
                            type="submit"
                            class="bg-gradient-to-r from-blue-500 to-blue-900 text-white font-bold py-2 px-4 w-40 rounded-full focus:outline-none focus:ring focus:ring-blue-300"
                        >
                            Login
                        </button>
                    </div>
                    <div class="flex items-center justify-center mt-4">
                  <Link :href="route('register')"
                     class="inline-block align-baseline font-bold text-xs text-blue-600 hover:text-blue-800"
                    :disabled="form.processing"><u>
                    Create Account</u>
                  </Link>
                </div>
                </form>
            </div>
        </div>
    </div>
</template>
