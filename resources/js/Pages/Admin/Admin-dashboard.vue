<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ReportsChart from '../../Components/ReportsChart.vue';
import Layout from '../../Layouts/admin-layout.vue';

// Fetch data from props
const { props } = usePage();

// Data for ReportsChart
const allocations = ref(props.allocations || []);
const utilizations = ref(props.utilizations || []);

// Filtered data for tables and other components
const provinceData = ref(props.provinceData || []);
const programData = ref(props.programData || []);
const totalAllocation = ref(props.totalAllocation || 0);
const totalUtilization = ref(props.totalUtilization || 0);
const totalTarget = ref(props.totalTarget || 0);
const totalServed = ref(props.totalServed || 0);
const selectedYear = ref(props.selectedYear || new Date().getFullYear());
const selectedQuarter = ref(props.selectedQuarter || Math.ceil((new Date().getMonth() + 1) / 3));

// Define options for year and quarter
const years = [2022, 2023, 2024];  // Add as needed
const quarters = [1, 2, 3, 4];

// Function to reload the page with selected year and quarter
const applyFilter = () => {
  window.location.href = `?year=${selectedYear.value}&quarter=${selectedQuarter.value}`;
};

// Calculate percentage variance
const calculatePercentageVariance = (allocation, utilization) => {
  if (allocation === 0 && utilization === 0) {
    return '0%'; // No allocation and no utilization means no variance
  } else if (allocation > 0 && utilization === 0) {
    return '100%'; // 100% of the allocated funds were not used
  } else if (allocation === 0) {
    return '0%'; // No allocation means no variance
  }

  const variance = ((allocation - utilization) / allocation) * 100;
  return variance.toFixed(2) + '%'; // Calculate remaining unused funds as variance
};

// Function to format numbers to display with K for thousands and M for millions
const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(2) + 'M'; // Converts to millions and adds 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(2) + 'K'; // Converts to thousands and adds 'K'
  } else {
    return num; // Returns the number as is if less than 1000
  }
};

onMounted(() => {
  console.log('Allocations in Admin-dashboard:', allocations.value);
  console.log('Utilizations in Admin-dashboard:', utilizations.value);
});

const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};
</script>

<template>
  <div class="flex">
    <Layout @sidebar-expanded="handleSidebarExpanded" />
    <div
      :class="{
        'ml-60': isSidebarExpanded,
        'ml-16': !isSidebarExpanded,
      }"
      class="flex-1 p-4 transition-all duration-300 bg-gray-100"
    >
      <!-- Header and Filter Section inside a single flex container -->
      <header class="bg-white shadow-sm w-full max-w-6xl mx-auto mb-6">
        <div class="py-4 px-4 sm:px-6 lg:px-4">
          <div class="flex justify-between items-center">
            <!-- Filter Section (Inline with Title) -->
            <div class="flex items-center space-x-4">
              <select v-model="selectedYear" class="p-2 border rounded">
                <option v-for="year in years" :value="year" :key="year">{{ year }}</option>
              </select>
              <select v-model="selectedQuarter" class="p-2 border rounded">
                <option v-for="quarter in quarters" :value="quarter" :key="quarter">Quarter {{ quarter }}</option>
              </select>
              <button
                @click="applyFilter"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"
              >
                Apply Filter
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Summary cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 w-full max-w-6xl mx-auto">
        <div class="bg-white p-4 rounded shadow text-center">
          <h3 class="text-gray-500">Total Allocation</h3>
          <p class="text-2xl font-bold">{{ formatNumber(totalAllocation) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <h3 class="text-gray-500">Total Utilized</h3>
          <p class="text-2xl font-bold">{{ formatNumber(totalUtilization) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <h3 class="text-gray-500">Total Target</h3>
          <p class="text-2xl font-bold">{{ formatNumber(totalTarget) }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <h3 class="text-gray-500">Total Served</h3>
          <p class="text-2xl font-bold">{{ formatNumber(totalServed) }}</p>
        </div>
      </div>

      <!-- Bar Graph for Fund Distribution Over Time -->
      <div class="bg-white p-4 rounded shadow mb-4 w-full max-w-6xl mx-auto">
        <h3 class="text-center text-xl font-bold mb-4">Fund Distribution Over Time</h3>
        <div class="h-auto w-full">
          <ReportsChart
            :allocations="allocations"
            :utilizations="utilizations"
            :selectedYear="selectedYear"
            :selectedQuarter="selectedQuarter"
          />
        </div>
      </div>

      <!-- Side by side layout for tables -->
      <div class="flex flex-col md:flex-row gap-4 mb-4 w-full max-w-6xl mx-auto">
        <!-- Fund Allocation Distribution for Programs -->
        <div class="bg-white p-4 rounded shadow flex-1 overflow-auto">
          <h3 class="text-center text-xl font-bold mb-4">Fund Allocation Distribution by Program</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="program in programData" :key="program.program">
                  <td class="px-4 py-4 whitespace-nowrap">{{ program.program }}</td>
                  <td class="px-2 py-1 whitespace-nowrap">{{ calculatePercentageVariance(program.total_allocation, program.total_utilization) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Fund Allocation Distribution and Provinces Table -->
        <div class="bg-white p-4 rounded shadow flex-1 overflow-auto">
          <h3 class="text-center text-xl font-bold mb-4">Fund Allocation and Utilization per Province</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Province</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="province in provinceData" :key="province.province">
                  <td class="px-6 py-4 whitespace-nowrap">{{ province.province }}</td>
                  <td class="px-4 py-1 whitespace-nowrap">{{ calculatePercentageVariance(province.total_allocation, province.total_utilization) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
