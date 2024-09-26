<script setup>
import { ref, onMounted, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import Layout from '../../Layouts/admin-layout.vue';

const showFormModal = ref(false);
const showStatusModal = ref(false); 
const editProgram = ref(null);
const programs = ref([]);
const showSuccessModal = ref(false);
const errors = ref({});
const successMessage = ref(''); // Success message state

const form = ref({
  name: '',
  logo: null,
  status: '0',
});

const statusForm = ref({
  status: '0', // Default to "unrestrict"
}); // Status form for separate modal

const searchQuery = ref(''); // Search query state

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10); // Show 5 programs per page

// Fetch programs function
const fetchPrograms = async () => {
  try {
    const response = await fetch('/programs'); // Adjust the endpoint to match your setup
    if (!response.ok) throw new Error('Failed to fetch programs');
    const data = await response.json();
    programs.value = data;
  } catch (error) {
    console.error('Error fetching programs:', error);
  }
};

// Computed property to filter and paginate programs
const filteredPrograms = computed(() => {
  const filtered = programs.value.filter(program => 
    program.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filtered.slice(start, end);
});

// Total pages calculation
const totalPages = computed(() => {
  const filtered = programs.value.filter(program => 
    program.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
  return Math.ceil(filtered.length / itemsPerPage.value);
});

// Pagination methods
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const openFormModal = (program = null) => {
  editProgram.value = program;
  form.value.name = program ? program.name : '';
  form.value.logo = null;
  showFormModal.value = true;
};

const openStatusModal = (program) => {
  editProgram.value = program;
  statusForm.value.status = program.status; // Set the current status
  showStatusModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  fetchPrograms(); // Refresh the list of programs after closing the modal
};

const closeStatusModal = () => {
  showStatusModal.value = false;
  fetchPrograms(); // Refresh the list of programs after closing the status modal
};

const checkProgramExists = (name) => {
  return programs.value.some(program => program.name === name && (!editProgram.value || program.id !== editProgram.value.id));
};

const submitForm = async () => {
  if (checkProgramExists(form.value.name)) {
    errors.value.name = 'A program with this name already exists.';
    return;
  } else {
    errors.value.name = null;
  }

  const data = new FormData();
  data.append('name', form.value.name);
  if (form.value.logo) {
    data.append('logo', form.value.logo);
  }

  if (editProgram.value) {
    Inertia.post(route('programs.update', editProgram.value.id), data, {
      onError: (err) => {
        errors.value = err;
      },
      onFinish: () => {
        successMessage.value = 'Updated Successfully';  // Set success message for update
        showSuccessModal.value = true;
        closeFormModal();
      }
    });
  } else {
    Inertia.post(route('programs.store'), data, {
      onError: (err) => {
        errors.value = err;
      },
      onFinish: () => {
        successMessage.value = 'Added Successfully';  // Set success message for add
        showSuccessModal.value = true;
        closeFormModal();
      }
    });
  }
};

const submitStatusForm = async () => {
  const data = new FormData();
  data.append('status', statusForm.value.status); // Only submit the status

  Inertia.post(route('programs.updateStatus', editProgram.value.id), data, {
    onError: (err) => {
      errors.value = err;
    },
    onFinish: () => {
      // Check the current status and set the appropriate success message
      if (statusForm.value.status === '1') {
        successMessage.value = 'Restricted Successfully';
      } else {
        successMessage.value = 'Unrestricted Successfully';
      }
      showSuccessModal.value = true;
      closeStatusModal();
    }
  });
};

onMounted(() => {
  fetchPrograms();
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
</style>

<template>
    <Layout @sidebar-expanded="handleSidebarExpanded"/>
    <div
      :class="{
        'ml-60': isSidebarExpanded,
        'ml-16': !isSidebarExpanded
      }"
      class="flex-1 p-4 transition-all duration-300 bg-gray-100"
    >
      <main class="flex-1 w-full max-w-7xl mx-auto py-4">
        <div class="bg-white p-6 rounded shadow-md">
          <!-- Search bar and Add Program button -->
          <div class="mb-4 flex justify-between items-center">
            <!-- Align search on the left -->
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search Program"
              class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            />
            <!-- Align Add Program button on the right -->
            <button @click="openFormModal()" class="bg-blue-500 text-white px-4 py-2 rounded">
              Add New Program
            </button>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th> 
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th> 
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission Access</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <!-- Use paginatedPrograms for pagination -->
                <tr v-for="program in filteredPrograms" :key="program.id">
                  <td class="px-6 py-1 whitespace-nowrap">
                    <img
                      v-if="program.logo"
                      :src="`/${program.logo}`"
                      alt="Program Logo"
                      class="h-10 w-auto"
                      style="max-width: 100px; max-height: 60px;"
                    />
                  </td>
                  <td class="px-6 py-1 whitespace-nowrap">{{ program.name }}</td>
                  <td class="px-6 py-1 whitespace-nowrap">
                    <div @click="openStatusModal(program)" class="cursor-pointer">
                      <svg class="h-6 w-6 text-stone-900" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"/>
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <circle cx="12" cy="16" r="1"/>
                        <path d="M8 11v-5a4 4 0 0 1 8 0"/>
                      </svg>
                    </div>

                  </td>
                  <td class="px-6 py-1 whitespace-nowrap">
                    <div @click="openFormModal(program)" class="cursor-pointer align-center">
                      <svg class="h-6 w-6 text-stone-900" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"/>
                        <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3"/>
                        <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3"/>
                        <line x1="16" y1="5" x2="19" y2="8"/>
                      </svg>
                    </div>

                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div class="mt-4 flex justify-between items-center">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50"
            >
              &larr; Previous
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50"
            >
              Next &rarr;
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Program Form Modal -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-auto bg-smoke-light flex items-center justify-center">
      <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg">
        <div class="flex justify-between items-center pb-3">
          <p class="text-2xl font-bold">{{ editProgram ? 'Edit Program' : 'Add Program' }}</p>
        </div>
        <form @submit.prevent="submitForm" enctype="multipart/form-data">
          <div class="mb-4">
            <label class="block text-sm font-bold mb-2">Name</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"       
              required>
            <div v-if="errors.name" class="text-red-500 text-xs">{{ errors.name }}</div>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-bold mb-2">Logo</label>
            <input 
              @change="e => form.logo = e.target.files[0]" 
              type="file" 
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>

          <div class="flex items-center justify-center mt-2 mb-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
              {{ editProgram ? 'Update' : 'Submit' }}
            </button>
          </div>
          <div class="flex items-center justify-center mt-2">
            <button class="inline-block align-baseline font-bold text-sm text-red-600 hover:text-red-800" @click="closeFormModal">
              <u>Close</u>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Status Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-auto bg-smoke-light flex items-center justify-center">
      <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg">
        <div class="flex justify-between items-center pb-3">
          <p class="text-2xl font-bold">Change Status for {{ editProgram.name }}</p> <!-- Display program name -->
        </div>
        <form @submit.prevent="submitStatusForm">
          <div class="mb-4">
            <h3 class="block text-sm font-bold mb-2">Status</h3>
            <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white sm:flex dark:text-white">
              <li class="w-full">
                <div class="flex items-center ps-3">
                  <input 
                    id="restrict-radio" 
                    type="radio" 
                    value="1" 
                    v-model="statusForm.status" 
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                  <label for="restrict-radio" class="w-full py-3 ms-2 text-sm font-normal text-black">Restrict</label>
                </div>
              </li>
              <li class="w-full">
                <div class="flex items-center ps-3">
                  <input 
                    id="unrestrict-radio" 
                    type="radio" 
                    value="0" 
                    v-model="statusForm.status" 
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                  <label for="unrestrict-radio" class="w-full py-3 ms-2 text-sm font-normal text-black">Unrestrict</label>
                </div>
              </li>
            </ul>
          </div>
          <div class="flex items-center justify-center mt-2 mb-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
              Submit
            </button>
          </div>
          <div class="flex items-center justify-center mt-2">
            <button class="inline-block align-baseline font-bold text-sm text-red-600 hover:text-red-800" @click="closeStatusModal">
              <u>Close</u>
            </button>
          </div>
        </form>
      </div>
    </div>

<!-- Success Modal -->
<div v-if="showSuccessModal" class="fixed z-10 inset-0 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
      <h2 class="text-2xl font-bold mb-4">{{ successMessage }}</h2> <!-- Display dynamic success message -->
      <div class="flex justify-center"> <!-- Add flex and justify-center -->
        <button @click="showSuccessModal = false" class="bg-blue-500 text-white px-4 py-2 rounded">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

</template>
