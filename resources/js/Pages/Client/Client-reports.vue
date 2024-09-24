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
</style>

<template>
  <Layout />
  <div class="ml-60 flex flex-col min-h-screen bg-gray-100 p-6">
    
    <!-- Header -->  
    <header class="bg-white shadow-sm w-full max-w-7xl mx-auto">
      <div class="py-2 px-4 sm:px-6 lg:px-8 flex ">
        <!-- Display the logo only if it exists -->
        <img :src="`/${programLogo}`" alt="Program Logo" class="h-12 w-12" v-if="programLogo" />
        <h1 class="text-2xl font-semibold text-gray-900 py-2">{{ programName }}</h1> 
      </div>
    </header>

    <!-- Main content -->
    <main class="flex-1 w-full max-w-7xl mx-auto py-4">
      <div class="bg-white p-6 rounded shadow-md">
        
        <!-- Filter and Search -->
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
            <button @click="openAllocationForm()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-2 rounded">
              Allocation Form
            </button>
            <button @click="openUtilizationForm()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-2 rounded">
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
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Province</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City/Municipality</th>
                   
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target/Physical</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fund Allocated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="allocation in paginatedAllocations" :key="allocation.id">
                    <td class="border px-4 py-1">{{ allocation.province.col_province }}</td>
                    <td class="border px-4 py-1">{{ allocation.citymuni?.col_citymuni }}</td>
                  
                    <td class="border px-4 py-1">{{ allocation.target }}</td>
                    <td class="border px-4 py-1">₱ {{ allocation.fund_allocation }}</td>
                    <!-- Edit Button, hidden if program is restricted -->
                    <td class="px-6 py-1">
                      <button v-if="programStatus === 0" @click="editItem(allocation)" class="bg-blue-500 text-white px-2 py-1 rounded mr-2">
                        Edit
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
           <!-- Pagination Controls for Allocations -->
<div class="flex justify-center space-x-2 items-center mt-4">
  <button @click="prevAllocationPage" :disabled="currentAllocationPage === 1" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold text-sm py-1 px-2 rounded-l">
    &larr; Previous
  </button>
  <button @click="nextAllocationPage" :disabled="currentAllocationPage === allocationPageNumbers.length" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold text-sm py-1 px-2 rounded-r">
    Next &rarr;
  </button>
</div>
          </div>

        <!-- Utilizations Table -->
        <div class="bg-white p-4 rounded shadow-md">
          <h2 class="text-lg font-semibold mb-2">Utilization Reports</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Province</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City/Municipality</th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target/Physical</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fund Utilized</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="utilization in paginatedUtilizations" :key="utilization.id">
                  <td class="border px-4 py-1">{{ utilization.province.col_province }}</td>
            <td class="border px-4 py-1">{{ utilization.citymuni?.col_citymuni }} </td>
                  <td class="border px-4 py-1">{{ utilization.physical }}</td>
                  <td class="border px-4 py-1">₱ {{ utilization.fund_utilized }}</td>
                  <!-- Edit Button, hidden if program is restricted -->
                  <td class="px-6 py-1">
                      <button v-if="programStatus === 0" @click="editItem(utilization)" class="bg-blue-500 text-white px-2 py-1 rounded mr-2">
                        Edit
                      </button>
                    </td>
                </tr>
              </tbody>
            </table>
          </div>
      <!-- Pagination Controls for Utilizations -->
<div class="flex justify-center space-x-2 items-center mt-4">
  <button @click="prevUtilizationPage" :disabled="currentUtilizationPage === 1" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold text-sm py-1 px-2 rounded-l">
    &larr; Previous
  </button>
  <button @click="nextUtilizationPage" :disabled="currentUtilizationPage === utilizationPageNumbers.length" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold text-sm py-1 px-2 rounded-r">
    Next &rarr;
  </button>
</div>
        </div>
      </div>
    </main>
  </div>
</template>
