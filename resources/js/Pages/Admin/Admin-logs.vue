<script setup>
import Layout from '../../Layouts/admin-layout.vue';
import { ref, computed } from 'vue'; // Import `computed` for pagination

const today = new Date().toISOString().split('T')[0];

const props = defineProps({
  logs: Array, // logs passed as props
});

const logs = ref(props.logs);

const currentPage = ref(1); // Set initial page to 1
const pageSize = 8; // Show 5 logs per page

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
      class="flex-1 p-4 transition-all duration-300 bg-gray-100"
    >
    <!-- Main content -->
    <main class="flex-1 w-full max-w-7xl mx-auto py-4"> <!-- Adjusted padding from 'py-6' to 'py-2' to remove top space -->
      <div class="bg-white p-6 rounded-lg shadow-lg"> <!-- Reduced padding inside the content from 'p-6' to 'p-4' -->
        
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
          <table class="min-w-full bg-white border border-gray-200 rounded-lg divide-y divide-gray-200 shadow-lg">
            <thead class="bg-blue-100 text-left">
              <tr>
                <th class="px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-1 text-gray-800 font-semibold">{{ new Date(log.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-1 text-gray-800 whitespace-nowrap overflow-hidden text-ellipsis">{{ log.user.first_name }} {{ log.user.last_name }}</td>
                <td class="px-6 py-1 text-gray-800">{{ log.action }}: {{ log.previous_value }} -> {{ log.new_value }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination controls -->
        <div class="mt-6 flex justify-between items-center">
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50"
          >
            &larr; Previous
          </button>

          <!-- Removed page number display here -->

          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 disabled:opacity-50"
          >
            Next &rarr;
          </button>
        </div>
      </div>
    </main>
  </div>
</template>
