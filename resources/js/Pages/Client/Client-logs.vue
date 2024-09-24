<script setup>
import Layout from '../../Layouts/client-layout.vue';
import { ref, computed } from 'vue';

const today = new Date().toISOString().split('T')[0]; 

const props = defineProps({
  logs: Array,
});

const logs = ref(props.logs);

// Pagination state
const currentPage = ref(1);
const pageSize = ref(10); // Set page size to 5 logs per page

// Search state
const searchDate = ref('');

// Updated computed filtered logs based on search query
const filteredLogs = computed(() => {
  if (!searchDate.value) return logs.value;

  // Ensure the format matches the search input (YYYY-MM-DD)
  return logs.value.filter(log => {
    const logDate = new Date(log.created_at).toISOString().split('T')[0]; // Extract the date in 'YYYY-MM-DD' format
    return logDate === searchDate.value;
  });
});

// Paginated logs
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLogs.value.slice(start, end);
});

// Total pages calculation
const totalPages = computed(() => {
  return Math.ceil(filteredLogs.value.length / pageSize.value);
});

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Generate page numbers
const pageNumbers = computed(() => {
  return Array.from({ length: totalPages.value }, (_, i) => i + 1);
});
</script>

<template>
  <Layout />
  <div class="ml-60 flex flex-col min-h-screen bg-gray-100 p-6">
    <!-- Header -->
    <header class="bg-white shadow-sm w-full max-w-7xl mx-auto">
      <div class="py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Logs</h1>
      </div>
    </header>

    <!-- Main content -->
    <main class="flex-1 w-full max-w-7xl mx-auto py-6">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        
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
          <table class="min-w-full bg-white border border-gray-200 rounded-lg divide-y divide-gray-200 shadow-sm">
            <thead class="bg-gray-50 text-left">
              <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Form Type</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 text-gray-800">{{ new Date(log.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-4 text-gray-800">{{ log.user.first_name }} {{ log.user.last_name }}</td>
                <td class="px-6 py-4 text-gray-800 capitalize">{{ log.action }} {{ log.type }} for</td>
                <td class="px-6 py-4 text-gray-800">
                  {{ log.city_municipality }} 
                  <span v-if="log.city_municipality && log.province">,</span>
                  {{ log.province }}
                </td>
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
