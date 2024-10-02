<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
  first_name: '',
  middle_name: '',
  last_name: '',
  suffix: '',
  role: '',
  email: '',
  employeeid: '',
  program: '',
  password: '',
  password_confirmation: '',
  approved: '',
});

const { props } = usePage();
const programs = ref(props.programs);

const validatePassword = () => {
  const password = form.password;
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

  if (!passwordRegex.test(password)) {
    form.errors.password = 'Password must be at least 8 characters long, with at least one uppercase letter, one lowercase letter, one number, and one special character.';
    return false;
  } else {
    form.errors.password = '';
    return true;
  }
};

const validateEmployeeId = () => {
  const id = form.employeeid;
  
  // Validate that there is only one dash and not consecutive dashes
  const hasDoubleDash = /--/.test(id);
  const hasMoreThanOneDash = (id.match(/-/g) || []).length > 1;

  if (hasDoubleDash || hasMoreThanOneDash) {
    form.errors.employeeid = "ID Number must contain only one dash and no consecutive dashes.";
    return false;
  } else {
    form.errors.employeeid = "";
    return true;
  }
};

const submit = () => {
  // Perform validation for password and employee ID
  const isPasswordValid = validatePassword();
  const isEmployeeIdValid = validateEmployeeId();

  if (isPasswordValid && isEmployeeIdValid) {
    form.post('/register', {
      onError: () => form.reset('password', 'password_confirmation'),
    });
  }
};

const imageName = 'dswd-logo-transparent.png';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-cover bg-center p-2 sm:p-4" style="background-image: url('/ppd-images/bg-image.jpg')">
    <div class="bg-white bg-opacity-75 px-3 sm:px-4 py-2 rounded shadow-lg w-full max-w-xs sm:max-w-sm">
      <div class="flex justify-center mb-1">
        <img
          :src="`/ppd-images/${imageName}`"
          alt="DSWD Logo"
          class="h-25 w-auto"
        />
      </div>

      <form @submit.prevent="submit" class="space-y-2">
        <!-- First Name and Middle Name Side by Side -->
        <div class="flex space-x-2">
          <div class="w-1/2">
            <label class="block text-sm font-bold mb-1">First Name</label>
            <input name="first_name" v-model="form.first_name" 
              class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
              placeholder="e.g. Juan"
              pattern="[A-Za-z\s\-]+">
            <div v-if="form.errors.first_name" class="text-red-500 text-xs">{{ form.errors.first_name }}</div>
          </div>
          <div class="w-1/2">
            <label class="block text-sm font-bold mb-1">Middle Name</label>
            <input name="middle_name" v-model="form.middle_name" 
              class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
              placeholder="e.g. Allan"
              pattern="[A-Za-z\s\-]+">
            <div v-if="form.errors.middle_name" class="text-red-500 text-xs">{{ form.errors.middle_name }}</div>
          </div>
        </div>

        <!-- Last Name and Suffix Side by Side -->
        <div class="flex space-x-2">
          <div class="w-2/3">
            <label class="block text-sm font-bold mb-1">Last Name</label>
            <input name="last_name" v-model="form.last_name" 
              class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
              placeholder="e.g. Dela Cruz"
              pattern="[A-Za-z\s\-]+">
            <div v-if="form.errors.last_name" class="text-red-500 text-xs">{{ form.errors.last_name }}</div>
          </div>
          <div class="w-1/3">
            <label class="block text-sm font-bold mb-1">Suffix</label>
            <input name="suffix" v-model="form.suffix" 
              class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
              placeholder="e.g. III"
              pattern="[A-Za-z]{1,5}\.?([A-Za-z\s\-]{1,5})?">
            <div v-if="form.errors.suffix" class="text-red-500 text-xs">{{ form.errors.suffix }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-bold mb-1">Role</label>
          <select v-model="form.role" id="role" name="role"
          class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm" required>
              <option value="">Select Role</option>
              <option value="0">User</option>
              <option value="1">Admin</option>
           </select>
          <div v-if="form.errors.role" class="text-red-500 text-xs">{{ form.errors.role }}</div>
        </div>

        <!-- Program Selection, Hidden if Role is Admin -->
        <div v-if="form.role !== 'admin'">
          <label class="block text-sm font-bold mb-1">Program</label>
           <select v-model="form.program" id="program" name="program"
           class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm" required>
            <option value="">Select Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.name">{{ program.name }}</option>
           </select>
         <div v-if="form.errors.program" class="text-red-500 text-xs">{{ form.errors.program }}</div>
        </div>

        <div>
          <label class="block text-sm font-bold mb-1">Email</label>
          <input name="email" v-model="form.email" 
           type="email" 
           class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
           placeholder="Email Address"
            pattern="[a-zA-Z0-9._%+-]+@(gmail\.com|dswd\.gov\.ph)$"
            title="Please enter a valid email address (e.g., user@gmail.com or user@dswd.gov.ph)">
          <div v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="block text-sm font-bold mb-1">ID No.</label>
          <input name="employeeid" v-model="form.employeeid" type="text"
            class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
            placeholder="ID Number"
            @input="validateEmployeeId"
            pattern="[0-9\-]+">
          <div v-if="form.errors.employeeid" class="text-red-500 text-xs">{{ form.errors.employeeid }}</div>
        </div>

        <div>
          <label class="block text-sm font-bold mb-1">Password</label>
          <input name="password" v-model="form.password" type="password" 
          class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
          placeholder="Password"
          @blur="validatePassword">
          <div v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}</div>
        </div>

        <div>
          <label class="block text-sm font-bold mb-1">Re-type Password</label>
          <input name="password_confirmation" v-model="form.password_confirmation" type="password" 
            class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
            placeholder="Re-type Password">
          <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs">{{ form.errors.password_confirmation }}</div>
        </div>

        <div class="flex items-center justify-center mt-2">
          <button type="submit"
          class="bg-gradient-to-r from-blue-500 to-blue-900 text-white font-bold py-1.5 px-6 rounded-full focus:outline-none focus:shadow-outline text-sm">
            Register
          </button>
        </div>

        <div class="flex items-center justify-center mt-4">
          <Link :href="route('login')"
            class="inline-block align-baseline font-bold text-xs text-blue-600 hover:text-blue-800"
            :disabled="form.processing"><u>
            Sign In</u>
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>
