<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';

// Define props to receive data from parent
const props = defineProps({
  show: Boolean,
  editingItem: Object,
  programName: String,
  programStatus: Number, // Status to check if the program is restricted
});

// Emits to communicate with parent component
const emit = defineEmits(['close', 'formSubmitted']);

// Reactive form state
const form = reactive({
  province: '',
  city_municipality: '',
  program: '',
  physical: '',
  fund_utilized: '',
});

const errorMessage = ref(''); // To store error message
const showSuccessModal = ref(false);

// Dropdown data for provinces and cities
const provinces = ref([]);
const cities = ref([]);

// Fetch provinces and user's program on component mount
onMounted(async () => {
  await fetchProvinces();
  if (props.editingItem) {
    await fetchCities(props.editingItem.province); // Fetch cities when editing
  }
  await fetchUserProgram(); // Fetch user's program on mount
});

// Fetch user's program based on their program_id
const fetchUserProgram = async () => {
  try {
    const userResponse = await fetch('/user');
    const userData = await userResponse.json();
    const programId = userData.program_id;

    if (programId) {
      const programResponse = await fetch(`/programs/${programId}`);
      const programData = await programResponse.json();

      // Populate program data
      form.program = programData.name;  // Assign program name
      programStatus = programData.status;  // Correct usage of programStatus
    }
  } catch (error) {
    console.error('Error fetching user program:', error);
  }
};


// Fetch provinces from the API
const fetchProvinces = async () => {
  try {
    const response = await fetch('/address/provinces');
    provinces.value = await response.json();
  } catch (error) {
    console.error('Error fetching provinces:', error);
  }
};

const fetchCities = async (provincePsgc) => {
  try {
    const response = await fetch(`/address/cities/${provincePsgc}`);
    const data = await response.json();
    cities.value = data; // Assign cities to the reactive variable
    return cities.value;
  } catch (error) {
    console.error('Error fetching cities:', error);
    return [];
  }
};

watch(() => form.province, async (newProvince) => {
  if (newProvince) {
    const selectedProvince = provinces.value.find(province => province.col_province === newProvince);
    if (selectedProvince) {
      await fetchCities(selectedProvince.psgc); // Fetch cities based on selected province
    }
  }
});

// Reset form fields
const resetForm = () => {
  form.province = '';
  form.city_municipality = '';
  form.program = '';
  form.physical = '';
  form.fund_utilized = '';
};

watch(() => props.editingItem, async (newItem) => {
  if (newItem) {
    console.log('Editing Item:', newItem);

    // Set the province
    form.province = newItem.province.col_province;

    // Fetch cities based on the selected province PSGC
    const fetchedCities = await fetchCities(newItem.province.psgc);
    console.log('Fetched Cities:', fetchedCities);

    const newItemCityMunicipalityPSGC = String(newItem.city_municipality).trim();

    // Handle special cases for Davao City's congressional districts
    if (newItem.province.psgc === '112402000') { // PSGC code for Davao City
      if (newItem.city_municipality === '1') {
        form.city_municipality = '1st Congressional District';
      } else if (newItem.city_municipality === '2') {
        form.city_municipality = '2nd Congressional District';
      } else if (newItem.city_municipality === '3') {
        form.city_municipality = '3rd Congressional District';
      } else {
        form.city_municipality = '';
        console.warn('No city matched for Davao City Congressional District PSGC:', newItem.city_municipality);
      }
    } else {
      // For other provinces, match the city PSGC with the city_municipality in newItem
      const matchedCity = fetchedCities.find(city => String(city.psgc).trim() === String(newItem.city_municipality).trim());

      if (matchedCity) {
        form.city_municipality = matchedCity.col_citymuni; // Match by PSGC
        console.log('Matched City:', matchedCity);
      } else {
        console.warn(`No city matched for PSGC: ${newItem.city_municipality}. Available cities:`, fetchedCities);
        form.city_municipality = ''; // Reset if no match found
      }
    }

    console.log('Pre-filled City/Municipality:', form.city_municipality);

    // Pre-fill other fields
    form.program = props.programName;
    form.physical = newItem.physical;
    form.fund_utilized = newItem.fund_utilized;
  } else {
    resetForm(); // Reset if no newItem
  }
}, { immediate: true });

// Submit form logic
const submitForm = async () => {
  // Check if the program is restricted before allowing submission
  if (props.programStatus === 1) {
    errorMessage.value = "Program is restricted, submission not allowed.";  // Set the error message
    return;  // Block form submission
  }

   // Check for zero values
   if (form.physical <= 0 || form.fund_utilized <= 0) {
    errorMessage.value = "Physical and Fund Utilized cannot be zero or less.";
    return;
  }

  form.program = props.programName;

  try {
    if (props.editingItem && props.editingItem.id) {
      await Inertia.post(route('utilizations.update', props.editingItem.id), form);
      emit('formSubmitted', 'Utilization Report Updated Successfully!');
    } else {
      await Inertia.post('/utilizations', form);
      resetForm();
      emit('formSubmitted', 'Utilization Report Created Successfully!');
    }
  } catch (error) {
    console.error('Error during form submission:', error);
  }
};

// Close the modal
const close = () => {
  emit('close');
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-auto bg-smoke-light flex">
    <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg">
      <div class="flex justify-between items-center pb-3">
        <p class="text-2xl font-bold">Utilization Form</p>
        <button class="z-50" @click="close">Close</button>
      </div>

      <!-- Error message if submission is restricted -->
      <div v-if="errorMessage" class="text-red-500 text-center mb-4">{{ errorMessage }}</div>

      <form @submit.prevent="submitForm">
        <!-- Province Dropdown -->
        <div class="mb-2">
          <label class="block text-sm font-bold mb-1">Province</label>
          <select v-model="form.province" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            <option value="">Select a province</option>
            <option v-for="province in provinces" :key="province.psgc" :value="province.col_province">{{ province.col_province }}</option>
          </select>
        </div>

     <!-- City/Municipality Dropdown -->
     <div class="mb-2">
          <label class="block text-sm font-bold mb-1">City/Municipality</label>
          <select v-model="form.city_municipality" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            <option value="">Select a City/Municipality</option>
            <option v-for="city in cities" :key="city.psgc" :value="city.col_citymuni">
              {{ city.col_citymuni }}
            </option>
          </select>
        </div>

        <!-- Program Input (Read-only) -->
        <div class="mb-2">
          <label class="block text-sm font-bold mb-1">Program</label>
          <input :value="programName" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" readonly />
        </div>

        <!-- Physical Input -->
        <div class="mb-2">
          <label class="block text-sm font-bold mb-1">Physical</label>
          <input v-model="form.physical" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <!-- Fund Utilized Input -->
        <div class="mb-2">
          <label class="block text-sm font-bold mb-1">Fund Utilized</label>
          <input v-model="form.fund_utilized" type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <!-- Submit button or restricted message -->
        <div class="flex items-center justify-center mt-4">
          <button 
            v-if="programStatus !== 1"
            type="submit" 
            class="bg-blue-500 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline"
          >
            Submit
          </button>
          <!-- Show message when submission is restricted -->
          <p v-if="programStatus === 1" class="text-red-500 font-bold mt-2">
            Submission is restricted
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.bg-smoke-light {
  background: rgba(0, 0, 0, 0.5);
}
</style>
