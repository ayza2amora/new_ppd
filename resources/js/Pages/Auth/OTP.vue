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
            if (userRole !== undefined) { // Ensure userRole is defined
                if (userRole === '1') { // Admin
                    setTimeout(() => {
                        router.visit(route("admin-dashboard")); // Redirect to admin dashboard
                    }, 1500);
                } else if (userRole === '0') { // Regular user
                    setTimeout(() => {
                        router.visit(route("client-reports")); // Redirect to client reports for regular users
                    }, 1500);
                } else {
                    setErrorMessage("Invalid user role. Please try again.");
                }
            } else {
                setErrorMessage("User role not found. Please try again.");
            }
        },
        onError: (errors) => {
            setErrorMessage(errors.otp || "Invalid OTP. Please try again.");
        },
    });
};

const setErrorMessage = (message) => {
    errorMessage.value = message;  // This will trigger the modal to appear
    clearNotificationMessage();  // Clear any notification messages
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

const countdown = ref(300); // 5 minutes in seconds

// Start countdown when component is mounted
const startCountdown = () => {
    const countdownInterval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(countdownInterval);
            setErrorMessage("OTP has expired. Please request a new one.");
            form.otp = Array(6).fill(""); // Reset OTP input
        }
    }, 1000);
};

// Display countdown in minutes and seconds format
const formattedCountdown = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
});

onMounted(() => {
    hasResent.value = false;
    sendOtp(); // Send OTP when the component is mounted
    startCountdown(); // Start the countdown when the page is loaded
});

</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-screen py-2 bg-cover bg-center" style="background-image: url('/ppd-images/bg-image.jpg')">
        
        <!-- Modal for Incorrect OTP -->
        <div v-if="errorMessage" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Invalid OTP</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">{{ errorMessage }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm" @click="clearErrorMessage">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- OTP Form with smaller width -->
        <div class="bg-white bg-opacity-75 px-6 pt-4 pb-4 rounded-xl shadow-lg w-full max-w-md mx-2"> <!-- Changed px-12 to px-6, max-w-xl to max-w-md -->
            <div class="flex justify-center mb-4">
                <img :src="`/ppd-images/${imageName}`" alt="DSWD Logo" class="h-full w-full inline-block" />
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
                        class="border-2 border-black w-10 h-12 text-2xl rounded-xl text-center" 
                        maxlength="1"
                        ref="otpInputs"
                        @input="handleInputChange(index, $event)"
                        @keydown.backspace="focusPreviousInput(index)"
                    />
                </div>

                <p class="text-lg text-gray-700 mt-2 mb-2">
                    Time remaining: <span class="font-bold">{{ formattedCountdown }}</span>
                </p>

                <div class="flex justify-center items-center ">
                    <button
                        :disabled="isResending || form.processing || hasResent"
                        @click.prevent="resendOtp"
                        class="text-blue-500 text-base font-bold underline hover:text-blue-700"
                        :class="{ 'opacity-50 cursor-not-allowed': isResending || form.processing || hasResent }"
                    >
                        {{ resendButtonText }}
                    </button>
                </div>

              
                <div class="flex gap-2 justify-center mt-3">  
                    <button
                        type="submit"
                        class="w-1/2 bg-blue-400 text-black font-black py-1 rounded-full hover:bg-blue-900 hover:text-white transition duration-300 ease-in-out focus:outline-none text-lg"
                        :disabled="form.processing || !isValidOtp"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing || !isValidOtp }"
                    >
                        Submit
                    </button>
                    <button
                        type="button"
                        @click="cancel"
                        class="w-1/3 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:ring focus:ring-gray-300"
                    >
                        Cancel
                    </button>
                </div>
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
