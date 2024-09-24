<script setup>
import { ref, onMounted, nextTick, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";

// Initialize form with otp as an array
const form = useForm({
    otp: Array(6).fill(""), // Initialize otp as an array with empty strings
});
const imageName = 'dswd-logo-transparent.png';
const isSending = ref(false);
const isResending = ref(false);
const errorMessage = ref("");
const notificationMessage = ref("");
const hasResent = ref(false);
const resendCountdown = ref(30);

const otpDigits = computed(() => form.otp);

const isValidOtp = computed(() => {
    return form.otp.every(digit => digit !== "");
});

const resendButtonText = computed(() => {
    return hasResent.value ? `Resend in ${resendCountdown.value}s` : "Resend";
});

// Convert OTP array to string for submission
const otpAsString = computed(() => form.otp.join(""));

const verifyOtp = () => {
    if (!isValidOtp.value) {
        setErrorMessage("Please enter a valid 6-digit OTP.");
        return;
    }

    form.post(route("otp.verify"), {
    preserveState: true,
    preserveScroll: true,
    data: { otp: otpAsString.value }, // Send OTP as a string
    onSuccess: (response) => {
        // Log the response to check its structure
        console.log(response);

        // Safely access the flash message and user role
        const flashMessage = response?.props?.flash?.message || "OTP verification succeeded.";
        const userRole = response?.props?.user?.role;

        setNotificationMessage(flashMessage);

        // Check if userRole exists before proceeding
        if (userRole) {
            if (userRole === 'admin') {
                setTimeout(() => {
                    router.visit(route("admin-dashboard")); // Redirect to admin dashboard
                }, 1500);
            } else {
                setTimeout(() => {
                    router.visit(route("client-reports")); // Redirect to client reports for regular users
                }, 1500);
            }
        } else {
            setErrorMessage("User role not found. Please try again.");
        }
    },
    onError: (errors) => {
        setErrorMessage(errors.otp || "Invalid OTP. Please try again.");
    },
});
}
// Helper functions to set messages and clear them after a delay
const setErrorMessage = (message) => {
    errorMessage.value = message;
    clearNotificationMessage();
    setTimeout(() => errorMessage.value = "", 5000);
};

const setNotificationMessage = (message) => {
    notificationMessage.value = message;
    clearErrorMessage();
    setTimeout(() => notificationMessage.value = "", 5000);
};

const clearErrorMessage = () => errorMessage.value = "";
const clearNotificationMessage = () => notificationMessage.value = "";

// Send OTP
const sendOtp = async () => {
    if (isSending.value || form.processing) return;

    isSending.value = true;
    errorMessage.value = "";

    try {
        const response = await axios.post(route("otp.send"));
        notificationMessage.value = response.data.message;
    } catch (error) {
        errorMessage.value = error.response?.data?.error || "Failed to send OTP. Please try again.";
    } finally {
        isSending.value = false;
    }
};

// Resend OTP
const resendOtp = () => {
    if (isResending.value || form.processing || hasResent.value) return;

    isResending.value = true;
    hasResent.value = true;
    errorMessage.value = "";
    notificationMessage.value = "";

    axios.post(route("otp.resend"))
        .then((response) => {
            if (response.data.success) {
                notificationMessage.value = response.data.message;
                startResendCountdown();
            } else {
                setErrorMessage(response.data.message);
            }
        })
        .catch((error) => {
            setErrorMessage(error.response?.data?.message || "Failed to resend OTP. Please try again.");
        })
        .finally(() => isResending.value = false);
};

const startResendCountdown = () => {
    resendCountdown.value = 30;
    const countdownInterval = setInterval(() => {
        resendCountdown.value--;
        if (resendCountdown.value <= 0) {
            clearInterval(countdownInterval);
            hasResent.value = false;
        }
    }, 1000);
};

const handleInputChange = (index, event) => {
    const value = event.target.value;
    if (/^[0-9]?$/.test(value)) {
        form.otp[index] = value; // Update OTP array
        focusNextInput(index);
    }
};

const cancel = () => router.visit(route("login"));

const focusNextInput = (index) => {
    if (form.otp[index] && index < 5) {
        nextTick(() => {
            const inputs = document.querySelectorAll("input");
            if (inputs[index + 1]) {
                inputs[index + 1].focus();
            }
        });
    }
};

const focusPreviousInput = (index) => {
    if (form.otp[index] === "" && index > 0) {
        nextTick(() => {
            const inputs = document.querySelectorAll("input");
            if (inputs[index - 1]) {
                inputs[index - 1].focus();
            }
        });
    }
};

onMounted(() => {
    hasResent.value = false;
    sendOtp(); // Send OTP when the component is mounted
});

</script>

<template>
   
    <div class="flex flex-col items-center justify-center min-h-screen py-2 bg-cover bg-center" style="background-image: url('/ppd-images/bg-image.jpg')">
        <p class="p-4 bg-gray-200">{{ $page.props.flash.success }}</p>
        <div class="bg-white bg-opacity-75 px-12 pt-4 pb-2 rounded-xl shadow-lg w-full max-w-xl m-20">
            
            <div class="flex justify-center mb-4">
                <img
                    :src="`/ppd-images/${imageName}`"
                    alt="DSWD Logo"
                    class="h-full w-full inline-block"
                />
            </div>
           
            <form @submit.prevent="verifyOtp" class="text-center mb-8">
                <div class="mb-4 text-lg text-gray-700">
                    <p>Please enter the 6-digit Authentication PIN we sent to your email address:</p>
                </div>
                <div class="mt-2 flex flex-row gap-2 justify-center">
                    <input
                        v-for="(digit, index) in otpDigits"
                        :key="index"
                        :value="digit"
                        type="text"
                        class="border-2 border-black w-12 h-14 text-2xl rounded-xl text-center"
                        maxlength="1"
                        ref="otpInputs"
                        @input="handleInputChange(index, $event)"
                        @keydown.backspace="focusPreviousInput(index)"
                    />
                </div>
                <div class="flex gap-4 mt-3">
                    <button
                        type="submit"
                        class="w-1/2 bg-blue-400 text-black font-black content-center py-1 rounded-full hover:bg-blue-900 hover:text-white transition duration-300 ease-in-out focus:outline-none text-lg mx-auto block"
                        :disabled="form.processing || !isValidOtp"
                        :class="{
                            'opacity-50 cursor-not-allowed': form.processing || !isValidOtp,
                        }"
                    >
                        Submit
                    </button>
                    <button
                        type="button"
                        @click="cancel"
                        class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-full focus:outline-none focus:ring focus:ring-gray-300"
                    >
                        Cancel
                    </button>
                </div>
                <button
                    :disabled="isResending || form.processing || hasResent"
                    @click.prevent="resendOtp"
                    class="text-blue-500 text-base font-bold mt-4 block text-center underline hover:text-blue-700"
                    :class="{
                        'opacity-50 cursor-not-allowed': isResending || form.processing || hasResent,
                    }"
                >
                    {{ resendButtonText }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
