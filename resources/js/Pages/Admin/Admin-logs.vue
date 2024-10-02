<script setup>
import Layout from '../../Layouts/admin-layout.vue';
import { ref, computed } from 'vue'; // Import `computed` for pagination

const today = new Date().toISOString().split('T')[0];

const props = defineProps({
  logs: Array, // logs passed as props
});

const logs = ref(props.logs);

const currentPage = ref(1); // Set initial page to 1
const pageSize = 10; // Show 5 logs per page

const searchDate = ref('');

// Updated computed filtered logs based on search query
const filteredLogs = computed(() => {
  if (!searchDate.value) return logs.value;

  return logs.value.filter(log => {
    // Convert both dates to a consistent 'YYYY-MM-DD' format
    const logDate = new Date(log.created_at).toLocaleDateString('en-CA'); // Use 'en-CA' for 'YYYY-MM-DD' format
    return logDate === searchDate.value; // Compare with the search date
  });
});

// Paginated logs based on filtered logs
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  const end = start + pageSize;
  return filteredLogs.value.slice(start, end);
});

// Calculate total pages
const totalPages = computed(() => {
  return Math.ceil(logs.value.length / pageSize);
});

// Function to change page
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Generate page numbers array
const pageNumbers = computed(() => {
  return Array.from({ length: totalPages.value }, (_, i) => i + 1);
});

const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};
</script>

<template>
   <Layout @sidebar-expanded="handleSidebarExpanded"/>
    <div
      :class="{
        'ml-60': isSidebarExpanded,
        'ml-16': !isSidebarExpanded
      }"
     class="flex-1 min-h-screen p-4 transition-all duration-300 bg-gray-100" >

    <!-- Main content -->
    <div class="bg-white p-4 rounded shadow-md max-w-full"><!-- Reduced padding inside the content from 'p-6' to 'p-4' -->
        
        <!-- Date filter -->
        <div class="flex justify-start items-center mb-6">
          <label for="search" class="text-sm font-medium text-gray-700 mr-4">Filter by Date:</label>
          <input
            type="date"
            id="search"
            v-model="searchDate"
            :max="today"
            class="border border-gray-300 rounded-lg px-4 py-2 text-gray-700 shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
 <!-- Logs table -->
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white rounded-lg divide-y divide-gray-200 shadow-lg">
            <thead class="bg-blue-100 text-left">
              <tr>
                <th class="w-1/6 px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th> <!-- Set specific width -->
                <th class="w-1/6 px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">User</th> <!-- Set specific width -->
                <th class="w-4/6 px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Description</th> <!-- Set specific width -->
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-blue-50 transition-colors duration-200">
                <td class="w-1/6 px-6 py-3 text-gray-800 font-semibold">{{ new Date(log.created_at).toLocaleDateString() }}</td> <!-- Adjusted td to match th -->
                <td class="w-1/6 px-6 py-3 text-gray-800 whitespace-nowrap overflow-hidden text-ellipsis">{{ log.user.first_name }} {{ log.user.last_name }}</td> <!-- Adjusted td to match th -->
                <td class="w-4/6 px-6 py-3 text-gray-800">{{ log.action }}: {{ log.previous_value }} -> {{ log.new_value }}</td> <!-- Adjusted td to match th -->
              </tr>
            </tbody>
          </table>
        </div>

       <!-- Pagination controls -->
<div class="mt-6 flex justify-center space-x-4 items-center">
  <button
    @click="changePage(currentPage - 1)"
    :disabled="currentPage === 1"
    class="arrow-box"
    aria-label="Previous"
  >
    <span>&lt;</span> <!-- Left arrow < symbol -->
  </button>

  <span class="text-gray-600 font-medium text-center">
    {{ currentPage }} of {{ totalPages }}
  </span>

  <button
    @click="changePage(currentPage + 1)"
    :disabled="currentPage === totalPages"
    class="arrow-box"
    aria-label="Next"
  >
    <span>&gt;</span> <!-- Right arrow > symbol -->
  </button>
</div>
      </div>
    </div>
</template>

<style>
.arrow-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;  /* Adjust width */
  height: 30px; /* Adjust height */
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
