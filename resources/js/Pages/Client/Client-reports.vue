<script setup>
import { reactive, ref, onMounted, watch, computed } from 'vue';
import Layout from '../../Layouts/client-layout.vue';
import UtilizationForm from '../Client/UtilizationForm.vue';
import AllocationForm from '../Client/AllocationForm.vue';

const props = defineProps({
  show: Boolean,
  utilizations: Array,
  allocations: Array,
  programs:Array,
});

const allocations = ref([]);
const utilizations = ref([]);
const showAllocationForm = ref(false);
const showUtilizationForm = ref(false);
const editingItem = ref(null);
const programName = ref('');  // Program name
const programLogo = ref('');  // Program logo
const showSuccessModal = ref(false);  // Success modal state
const successMessage = ref('');
const programStatus = ref(0); // Program status (0 = unrestricted, 1 = restricted)
const searchDate = ref('');
const maxDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0]; // Format as yyyy-mm-dd
});

// Pagination state for allocations
const currentAllocationPage = ref(1);
const allocationItemsPerPage = 5;

// Pagination state for utilizations
const currentUtilizationPage = ref(1);
const utilizationItemsPerPage = 5;

const form = reactive({
  province: '',
  city_municipality: '',
  program: '',
  physical: '',
  fund_utilized: '',
});


const openAllocationForm = (allocation = null) => {
  if (allocation) {
    form.province = allocation.province || '';
    form.city_municipality = allocation.city_municipality || ''; // Bind city/municipality
    form.program = allocation.program || '';
    form.physical = allocation.target || ''; 
    form.fund_allocated = allocation.fund_allocation || '';
  }
  editingItem.value = allocation;
  showAllocationForm.value = true;
};

const closeAllocationForm = () => {
  console.log("Close Allocation Form clicked");
  editingItem.value = null;
  showAllocationForm.value = false;
};

const openUtilizationForm = (utilization = null) => {
  editingItem.value = utilization;
  showUtilizationForm.value = true;
};

const closeUtilizationForm = () => {
  editingItem.value = null;
  showUtilizationForm.value = false;
  fetchUtilizations();
};

const editItem = (item) => {
  editingItem.value = item; // Store the clicked item in editingItem
  if (item.hasOwnProperty('fund_allocation')) {
    openAllocationForm(item); // Open the allocation form with the selected item
  } else {
    openUtilizationForm(item); // Open the utilization form with the selected item
  }
};

const handleFormSubmitted = (message) => {
  successMessage.value = message;
  showSuccessModal.value = true;
  showAllocationForm.value = false;  // Hide the allocation form
  showUtilizationForm.value = false; // Hide the utilization form
};

// Re-fetch data after closing success modal
const closeSuccessModal = async () => {
  showSuccessModal.value = false;
  await fetchUtilizations();
  await fetchAllocations();
};

const fetchUtilizations = async () => {
  try {
    const response = await fetch('/utilizations');
    const data = await response.json();
    console.log('Utilizations fetched:', data.utilizations); // Log the response
    utilizations.value = data.utilizations;
  } catch (error) {
    console.error("Failed to fetch utilizations:", error);
  }
};

const fetchAllocations = async () => {
  try {
    const response = await fetch('/allocations');
    const data = await response.json();
    console.log('Allocations fetched:', data.allocations); // Log the response
    allocations.value = data.allocations;
  } catch (error) {
    console.error("Failed to fetch allocations:", error);
  }
};

const fetchUserProgram = async () => {
  try {
    const response = await fetch('/user'); // This should call getAuthenticatedUser in the controller
    const data = await response.json();

    console.log('User data fetched:', data); // Log all user data to ensure the status is fetched

    // Check if user has a program assigned and fetch program details
    if (data.program) {
      programName.value = data.program;
      programLogo.value = data.program_logo;
      programStatus.value = data.program_status; // Assign the program status
      console.log('Fetched Program Status:', programStatus.value); // Debug log
    }
  } catch (error) {
    console.error('Error fetching user program:', error);
  }
};

// Fetch reports based on search date
const searchReportsByDate = async () => {
  try {
    const formattedDate = searchDate.value ? new Date(searchDate.value).toISOString().split('T')[0] : '';
    const allocationsResponse = await fetch(`/allocations?date=${formattedDate}`);
    const utilizationsResponse = await fetch(`/utilizations?date=${formattedDate}`);

    allocations.value = (await allocationsResponse.json()).allocations;
    utilizations.value = (await utilizationsResponse.json()).utilizations;
  } catch (error) {
    console.error("Failed to search reports:", error);
  }
};

// Initialize component data
onMounted(() => {
  fetchAllocations();
  fetchUtilizations();
  fetchUserProgram();  // Fetch program name, logo, and status
});


watch(() => props.utilizations, (newValue) => {
  utilizations.value = newValue;
});

watch(() => props.allocations, (newValue) => {
  allocations.value = newValue;
});

// Computed properties for paginated allocations
const paginatedAllocations = computed(() => {
  const start = (currentAllocationPage.value - 1) * allocationItemsPerPage;
  return allocations.value.slice(start, start + allocationItemsPerPage);
});

// Computed properties for paginated utilizations
const paginatedUtilizations = computed(() => {
  const start = (currentUtilizationPage.value - 1) * utilizationItemsPerPage;
  return utilizations.value.slice(start, start + utilizationItemsPerPage);
});

// Total pages for allocations pagination
const totalAllocationPages = computed(() => {
  return Math.ceil(allocations.value.length / allocationItemsPerPage);
});

// Total pages for utilizations pagination
const totalUtilizationPages = computed(() => {
  return Math.ceil(utilizations.value.length / utilizationItemsPerPage);
});

// Methods for changing pages in allocations
const nextAllocationPage = () => {
  if (currentAllocationPage.value < totalAllocationPages.value) {
    currentAllocationPage.value++;
  }
};

const prevAllocationPage = () => {
  if (currentAllocationPage.value > 1) {
    currentAllocationPage.value--;
  }
};

// Methods for changing pages in utilizations
const nextUtilizationPage = () => {
  if (currentUtilizationPage.value < totalUtilizationPages.value) {
    currentUtilizationPage.value++;
  }
};

const prevUtilizationPage = () => {
  if (currentUtilizationPage.value > 1) {
    currentUtilizationPage.value--;
  }
};
// Generate page numbers for allocations
const allocationPageNumbers = computed(() => {
  const totalPages = Math.ceil(allocations.value.length / allocationItemsPerPage);
  return Array.from({ length: totalPages }, (_, i) => i + 1);
});

// Generate page numbers for utilizations
const utilizationPageNumbers = computed(() => {
  const totalPages = Math.ceil(utilizations.value.length / utilizationItemsPerPage);
  return Array.from({ length: totalPages }, (_, i) => i + 1);
});

const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};
</script>

<style scoped>
.bg-smoke-light {
  background: rgba(0, 0, 0, 0.5);
}

.border-blue-500 {
  border-color: #050505; 
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.button-improved {
  background-color: #4CAF50; /* A modern green color */
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem; /* Rounded corners */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow */
  transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth hover effects */
  font-weight: 600;
}

.button-improved:hover {
  background-color: #45a049; /* Slightly darker on hover */
  transform: translateY(-2px); /* Raise the button */
}

.button-improved:disabled {
  background-color: #ccc;
  color: #666;
  cursor: not-allowed;
}

.button-improved-blue {
  background-color: #007BFF; /* Bootstrap-like blue */
}

.button-improved-blue:hover {
  background-color: #0056b3; /* Darker on hover */
}

.arrow-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;  /* Reduced width */
  height: 30px; /* Reduced height */
  border: 1px solid #ccc; /* Light gray border */
  border-radius: 0px;  /* Rounded corners */
  background-color: #fff; /* White background */
  font-size: 1rem; /* Smaller font size */
  padding: 0.25rem;  /* Reduced padding */
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.arrow-box:hover {
  background-color: #f1f1f1; /* Slight background color change on hover */
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
}

.arrow-box:disabled {
  background-color: #f9f9f9; /* Lighter background when disabled */
  color: #ccc;  /* Gray out the arrow */
  cursor: not-allowed;
}

.arrow-box span {
  font-size: 1rem;  /* Adjusted font size to match button size */
  color: #333; /* Arrow color */
}
</style>

<template>
<Layout @sidebar-expanded="handleSidebarExpanded"/>
    <div
      :class="{
        'ml-60': isSidebarExpanded,
        'ml-16': !isSidebarExpanded
      }"
      class="flex-1 min-h-screen p-4 transition-all duration-300 bg-gray-100"
      >
    <!-- Main white container -->
    <div class="bg-white p-4 rounded shadow-md">
      
      <!-- Header with Program Logo and Name inside the white container -->
      <div class="flex items-center space-x-4 mb-4">
        <img :src="`/${programLogo}`" alt="Program Logo" class="h-12 w-12" v-if="programLogo" />
        <h1 class="text-2xl font-semibold text-gray-900">{{ programName }}</h1>
      </div>
      
      <!-- Filter and Form buttons -->
      <div class="flex justify-between mb-2">
        <!-- Date Picker and Search Button -->
        <div class="flex items-center space-x-2">
          <input v-model="searchDate" type="date" class="p-2 border rounded" :max="maxDate" placeholder="dd/mm/yyyy" />
          <button @click="searchReportsByDate" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Search
          </button>
        </div>

        <!-- Form buttons to open Allocation or Utilization forms -->
        <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
          <!-- Allocation Form Button -->
    <button @click="openAllocationForm()" class="button-improved button-improved-blue">
        Allocation Form
    </button>

<!-- Utilization Form Button -->
<button @click="openUtilizationForm()" class="button-improved button-improved-blue">
  Utilization Form
</button>
        </div>
      </div>

      <!-- Allocation and Utilization Forms -->
      <AllocationForm :show="showAllocationForm" :editing-item="editingItem" :programName="programName" :programStatus="programStatus" @close="closeAllocationForm" @formSubmitted="handleFormSubmitted" />
      <UtilizationForm :show="showUtilizationForm" :editing-item="editingItem" :programName="programName" :programStatus="programStatus" @close="closeUtilizationForm" @formSubmitted="handleFormSubmitted" />

      <!-- Success Modal -->
      <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-auto bg-smoke-light flex">
        <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg border border-blue-500 shadow-lg">
          <div class="flex justify-center pb-3">
            <p class="text-2xl font-bold text-blue-600">Success!</p>
          </div>
          <p class="text-center mb-4">{{ successMessage }}</p>
          <div class="flex items-center justify-center">
            <button @click="closeSuccessModal" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline">OK</button>
          </div>
        </div>
      </div>

      <!-- Allocations Table -->
      <div class="bg-white p-4 rounded shadow-md mb-6">
        <h2 class="text-lg font-semibold mb-2">Allocation Reports</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
  <thead class="bg-blue-100">
    <tr>
      <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th> <!-- New Date Created Column -->
      <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Province</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">City/Municipality</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target/Physical</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Fund Allocated</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
    </tr>
  </thead>
  <tbody class="bg-white divide-y divide-gray-200">
    <tr v-for="allocation in paginatedAllocations" :key="allocation.id">
      <td class="border px-4 py-1">{{ new Date(allocation.created_at).toLocaleDateString() }}</td> <!-- New Date Column -->
      <td class="border px-4 py-1">{{ allocation.province.col_province }}</td>
      <td class="border px-4 py-1">{{ allocation.citymuni?.col_citymuni }}</td>
      <td class="border px-4 py-1">{{ allocation.target }}</td>
      <td class="border px-4 py-1">₱ {{ allocation.fund_allocation }}</td>
       <td class="border px-6 py-1">
                  <div v-if="programStatus === 0" @click="editItem(allocation)" class="cursor-pointer flex justify-center items-center">
                    <svg class="h-6 w-6 text-stone-900" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z"/>
            <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3"/>
            <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3"/>
            <line x1="16" y1="5" x2="19" y2="8"/>
          </svg>
        </div>
        <div v-else class="cursor-pointer flex justify-center items-center">
          <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z"/>
            <rect x="5" y="11" width="14" height="10" rx="2"/>
            <circle cx="12" cy="16" r="1"/>
            <path d="M8 11v-5a4 4 0 0 1 8 0"/>
          </svg>
                  </div>

                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination Controls for Allocations -->
<div class="flex justify-center space-x-4 items-center mt-4">
  <button @click="prevAllocationPage" :disabled="currentAllocationPage === 1" class="arrow-box" aria-label="Previous">
    <span>&lt;</span> <!-- Replace with < symbol -->
  </button>

  <span class="text-gray-600 font-medium text-center"> 
    {{ currentAllocationPage }} of {{ totalAllocationPages }}
  </span>

  <button @click="nextAllocationPage" :disabled="currentAllocationPage === totalAllocationPages" class="arrow-box" aria-label="Next">
    <span>&gt;</span> <!-- Replace with > symbol -->
  </button>
</div>
      </div>

      <!-- Utilizations Table -->
      <div class="bg-white p-4 rounded shadow-md">
        <h2 class="text-lg font-semibold mb-2">Utilization Reports</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th> <!-- New Date Created Column -->
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Province</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">City/Municipality</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target/Physical</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Fund Utilized</th>
    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="utilization in paginatedUtilizations" :key="utilization.id">
                <td class="border px-4 py-1">{{ new Date(utilization.created_at).toLocaleDateString() }}</td> <!-- New Date Column -->
                <td class="border px-4 py-1">{{ utilization.province.col_province }}</td>
                <td class="border px-4 py-1">{{ utilization.citymuni?.col_citymuni }}</td>
                <td class="border px-4 py-1">{{ utilization.physical }}</td>
                <td class="border px-4 py-1">₱ {{ utilization.fund_utilized }}</td>
                <td class="border px-6 py-1">
                  <div v-if="programStatus === 0" @click="editItem(utilization)" class="cursor-pointer flex justify-center items-center">
                    <svg class="h-6 w-6 text-stone-900" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z"/>
            <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3"/>
            <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3"/>
            <line x1="16" y1="5" x2="19" y2="8"/>
          </svg>
        </div>
        <div v-else class="cursor-pointer flex justify-center items-center">
          <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z"/>
            <rect x="5" y="11" width="14" height="10" rx="2"/>
            <circle cx="12" cy="16" r="1"/>
            <path d="M8 11v-5a4 4 0 0 1 8 0"/>
          </svg>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
       <!-- Pagination Controls for Utilizations -->
<div class="flex justify-center space-x-4 items-center mt-4">
  <button @click="prevUtilizationPage" :disabled="currentUtilizationPage === 1" class="arrow-box" aria-label="Previous">
    <span>&lt;</span> <!-- Replace with < symbol -->
  </button>

  <span class="text-gray-600 font-medium text-center"> 
    {{ currentUtilizationPage }} of {{ totalUtilizationPages }}
  </span>

  <button @click="nextUtilizationPage" :disabled="currentUtilizationPage === totalUtilizationPages" class="arrow-box" aria-label="Next">
    <span>&gt;</span> <!-- Replace with > symbol -->
  </button>
</div>
      </div>

    </div> <!-- End of white container -->
  </div>
</template>
