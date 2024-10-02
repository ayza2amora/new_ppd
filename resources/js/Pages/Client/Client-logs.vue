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
const pageSize = ref(10);

// Search state
const searchDate = ref('');

// Updated computed filtered logs based on search query
const filteredLogs = computed(() => {
  if (!searchDate.value) return logs.value;

  return logs.value.filter(log => {
    const logDate = new Date(log.created_at).toISOString().split('T')[0];
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

const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};
</script>

<style>
.arrow-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;  /* Width of the box */
  height: 30px; /* Height of the box */
  border: 1px solid #ccc; /* Light gray border */
  border-radius: 5px;  /* Rounded corners */
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
  <Layout @sidebar-expanded="handleSidebarExpanded" />
  <div
    :class="{
      'ml-60': isSidebarExpanded,
      'ml-16': !isSidebarExpanded,
    }"
    class="flex-1 min-h-screen p-4 transition-all duration-300 bg-gray-100"
  >
<!-- Main white container with adjusted width -->
<div class="bg-white p-4 rounded shadow-md max-w-full">
        
        <!-- Date filter -->
        <div class="flex justify-start items-center mb-6">
          <label for="search" class="text-sm font-medium text-gray-700 mr-4">
            Filter by Date:
          </label>
          <div class="relative">
            <input
              type="date"
              id="search"
              v-model="searchDate"
              :max="today"
              class="border border-gray-300 rounded-lg px-6 py-2 text-gray-700 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Logs table -->
        <div class="overflow-x-auto">
          <table
            class="min-w-full bg-white rounded-lg divide-y divide-gray-200 shadow-lg"
          >
          <thead class="bg-blue-100 text-left">
    <tr>
      <th class="w-2/12 px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th> <!-- Added px-6 for uniform spacing -->
      <th class="w-2/12 px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">User</th> <!-- Matching px-6 for uniform spacing -->
      <th class="w-2/12 px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Form Type</th>
      <th class="w-2/12 px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Program</th>
      <th class="w-4/12 px-4 py-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Address</th>
    </tr>
  </thead>

 <!-- Table body -->
 <tbody class="divide-y divide-gray-200">
    <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-blue-50 transition-colors duration-200">
      <td class="w-1/12 px-4 py-3 text-gray-800 font-semibold">{{ new Date(log.created_at).toLocaleDateString() }}</td> <!-- Matching px-6 for uniform spacing -->
      <td class="w-2/12 px-4 py-3 text-gray-800">{{ log.user.first_name }} {{ log.user.last_name }}</td> <!-- Matching px-6 for uniform spacing -->
      <td class="w-2/12 px-4 py-3 text-gray-800 capitalize">{{ log.action }} {{ log.type }}</td>
      <td class="w-3/12 px-4 py-3 text-gray-800">{{ log.program }}</td>
      <td class="w-4/12 px-4 py-3 text-gray-800">{{ log.city_municipality }}<span v-if="log.city_municipality && log.province">, </span>{{ log.province }}</td>
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
    <span>&lt;</span> <!-- Replaced with '<' -->
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
    <span>&gt;</span> <!-- Replaced with '>' -->
  </button>
</div>
      </div>
  </div>
</template>
