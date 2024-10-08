<script setup>
import { ref, computed } from 'vue';
import { defineProps } from 'vue';
import Layout from '../../Layouts/admin-layout.vue';
import { generatePptReport } from './pptformat';
import { generateProgramPpt } from './pptprogram';

// State for the selected province
const selectedProvince = ref(null); // Initially no province is selected
const selectedProgram = ref(null); // Initially no program is selected

const generatePpt = () => {
  // Check if "All Provinces" is selected
  if (selectedProvince.value === 'all') {
    if (!props.provinces.length) {
      console.error('No provinces available to generate the report');
      return;
    }

    if (!props.programs.length) {
      console.error('No programs available to generate the report');
      return;
    }

    // Move Davao City to the top of the provinces list
    const sortedProvinces = [...props.provinces];
    const davaoCityIndex = sortedProvinces.findIndex(province => province.col_province.toLowerCase() === 'davao city');
    
    if (davaoCityIndex !== -1) {
      const davaoCity = sortedProvinces.splice(davaoCityIndex, 1)[0]; // Remove Davao City from the array
      sortedProvinces.unshift(davaoCity); // Add Davao City at the start
    }

    // Prepare the data for generating the report
    const allProvinces = sortedProvinces.map(province => {
      const groupedCityMunicipalities = province.districts || {};
      const allCityMunicipalities = Object.values(groupedCityMunicipalities).flatMap(districtGroup => {
        if (districtGroup.cities && Array.isArray(districtGroup.cities)) {
          return districtGroup.cities.map(city => city);
        }
        return [];
      });

      return {
        ...province,
        cities: allCityMunicipalities
      };
    });

    // Generate the report with sorted provinces (Davao City first)
    generatePptReport(allProvinces, props.programs, [], 'All Provinces');
  } else {
    // Handle single province selection
    if (!selectedProvince.value) {
      console.error('Please select a province to generate the report');
      return;
    }

    if (!props.programs.length) {
      console.error('No programs available to generate the report');
      return;
    }

    // Extract city/municipality data from the selected province
    const groupedCityMunicipalities = selectedProvince.value.districts || {};

    const allCityMunicipalities = Object.values(groupedCityMunicipalities).flatMap(districtGroup => {
      if (districtGroup.cities && Array.isArray(districtGroup.cities)) {
        return districtGroup.cities.map(city => city);
      }
      return [];
    });

    if (!allCityMunicipalities.length) {
      console.error('No city/municipality data available for the selected province');
      return;
    }

    // Generate the PowerPoint report with the extracted city/municipality data
    const provinceName = selectedProvince.value.col_province;

    // Passing province, programs, and cities to the generatePptReport function
    generatePptReport([selectedProvince.value], props.programs, allCityMunicipalities, provinceName);
  }
};

// New function for downloading program reports
const handleGeneratePpt = () => {
  // Check if "All Provinces" is selected
  if (selectedProvince.value === 'all') {
    if (!props.provinces.length) {
      console.error('No provinces available to generate the report');
      return;
    }

    if (!props.programs.length) {
      console.error('No programs available to generate the report');
      return;
    }

    // Move Davao City to the top of the provinces list
    const sortedProvinces = [...props.provinces];
    const davaoCityIndex = sortedProvinces.findIndex(province => province.col_province.toLowerCase() === 'davao city');

    if (davaoCityIndex !== -1) {
      const davaoCity = sortedProvinces.splice(davaoCityIndex, 1)[0]; // Remove Davao City from the array
      sortedProvinces.unshift(davaoCity); // Add Davao City at the start
    }

    // Prepare the data for generating the report
    const allProvinces = sortedProvinces.map(province => {
      const groupedCityMunicipalities = province.districts || {};
      const allCityMunicipalities = Object.values(groupedCityMunicipalities).flatMap(districtGroup => {
        if (districtGroup.cities && Array.isArray(districtGroup.cities)) {
          return districtGroup.cities.map(city => city);
        }
        return [];
      });

      return {
        ...province,
        cities: allCityMunicipalities
      };
    });

    // Pass the structured data along with programs to the generateProgramPpt function
    generateProgramPpt(allProvinces, props.programs, [], 'All Provinces');
  } else {
    // Handle single province selection
    if (!selectedProvince.value) {
      console.error('Please select a province to generate the report');
      return;
    }

    if (!props.programs.length) {
      console.error('No programs available to generate the report');
      return;
    }

    // Extract city/municipality data from the selected province
    const groupedCityMunicipalities = selectedProvince.value.districts || {};

    const allCityMunicipalities = Object.values(groupedCityMunicipalities).flatMap(districtGroup => {
      if (districtGroup.cities && Array.isArray(districtGroup.cities)) {
        return districtGroup.cities.map(city => city);
      }
      return [];
    });

    if (!allCityMunicipalities.length) {
      console.error('No city/municipality data available for the selected province');
      return;
    }
 // Generate the PowerPoint report with the extracted city/municipality data
 const provinceName = selectedProvince.value.col_province;

    // Pass the structured province data along with programs to the generateProgramPpt function
    generateProgramPpt([selectedProvince.value], props.programs, allCityMunicipalities, provinceName);
  }
};

// Function to convert district number to ordinal (1st, 2nd, 3rd, etc.)
const getOrdinal = (number) => {
  if (number === 1) return '1st';
  if (number === 2) return '2nd';
  if (number === 3) return '3rd';
  return `${number}th`; // For any other number (4th, 5th, etc.)
};

const props = defineProps({
  provinces: Array, // provinces with their associated districts and cities/municipalities
  programs: Array, // Array for programs data
});

// State to switch between views
const activeView = ref('provinces');

// Move Davao City to the top
const sortedProvinces = computed(() => {
  const provincesCopy = [...props.provinces];

  const davaoCityIndex = provincesCopy.findIndex(province => province.col_province.toLowerCase() === 'davao city');
  
  if (davaoCityIndex !== -1) {
    const davaoCity = provincesCopy.splice(davaoCityIndex, 1)[0]; 
    provincesCopy.unshift(davaoCity); 
  }

  return provincesCopy;
});

// State to track expanded provinces
const expandedProvince = ref(null);

const toggleProvince = (provincePsgc) => {
  expandedProvince.value = expandedProvince.value === provincePsgc ? null : provincePsgc;
};

// Function to calculate percentage variance
const calculatePercentageVariance = (value1, value2) => {
  if (value1 === undefined || value2 === undefined) return '0';
  if (value1 === 0 || value2 === 0) return '0'; // If either value is 0, return 0
  if (value1 === 0) return '0%'; 
  
  const variance = ((value1 - value2) / value1) * 100;
  return variance.toFixed(2) + '%';
};


// Search and download functionality
const searchQuery = ref('');
const fromDate = ref(''); // From date input
const toDate = ref(''); // To date input

// Filtered Provinces based on Search Query, From Date, and To Date
const filteredProvinces = computed(() => {
  let filtered = sortedProvinces.value;

  // Filter by search query (province name)
  if (searchQuery.value) {
    filtered = filtered.filter(province =>
      province.col_province.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  // Filter by from and to dates
  if (fromDate.value && toDate.value) {
    const from = new Date(fromDate.value);
    const to = new Date(toDate.value);

    filtered = filtered.filter(province => {
      const provinceDate = new Date(province.date);
      return provinceDate >= from && provinceDate <= to;
    });
  }

  return filtered;
});

// Filtered Programs based on selected program
const filteredPrograms = computed(() => {
  if (!selectedProgram.value) {
    return props.programs; // Show all programs if none is selected
  }
  return props.programs.filter(program => program.id === selectedProgram.value.id); // Filter based on selected program's ID
});

const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};

const selectedYear = ref(props.selectedYear || new Date().getFullYear());
const selectedQuarter = ref(props.selectedQuarter || Math.ceil((new Date().getMonth() + 1) / 3));

// Define options for year and quarter
const years = [2022, 2023, 2024];  // Add as needed
const quarters = [1, 2, 3, 4];

// Function to reload the page with selected year and quarter
const applyFilter = () => {
  window.location.href = `?year=${selectedYear.value}&quarter=${selectedQuarter.value}`;
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

      <div class="bg-white p-6 rounded shadow-md max-w-full mx-auto">
  
      
      <div class="flex justify-between mb-2 space-x-4">
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
        <!-- Province Selection Dropdown -->
      <div class="flex justify-between mb-4 space-x-4">     
        <select v-model="selectedProvince" class="px-4 py-2 border border-gray-300 rounded-md">
          <option value="" disabled>Select Province</option>
          <option value="all">All Provinces</option> <!-- Add option for all provinces -->
          <option v-for="province in sortedProvinces" :key="province.psgc" :value="province">
            {{ province.col_province }}
          </option>
        </select>

        <button
          @click="generatePpt"
          class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
          Province
        </button>
        <!-- New button for downloading program reports -->
        <button
            @click="handleGeneratePpt"
             class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            Program
          </button>

      </div>
    </div>
      <!-- Tabs for switching views -->
      <div class="flex space-x-4 border-b border-gray-200 mb-4">
        <button
          :class="{'border-b-2 border-blue-500 font-bold text-gray-900': activeView === 'provinces', 'text-gray-500': activeView !== 'provinces'}"
          class="py-2 px-4 focus:outline-none"
          @click="activeView = 'provinces'"
        >
          Provinces
        </button>
        <button
          :class="{'border-b-2 border-blue-500 font-bold text-gray-900': activeView === 'programs', 'text-gray-500': activeView !== 'programs'}"
          class="py-2 px-4 focus:outline-none"
          @click="activeView = 'programs'"
        >
          Programs
        </button>
      </div>

      <!-- Conditionally render tables based on activeView -->
      <div v-if="activeView === 'provinces'">
        <!-- Provinces Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Province</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Served</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Variance (Physical)</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Allocation</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Utilization</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Variance (Financial)</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <template v-for="province in filteredProvinces" :key="province.psgc">
                <tr>
                  <td class="px-8 py-2 whitespace-nowrap">
                    <div @click="toggleProvince(province.psgc)" class="cursor-pointer">
                      <strong>{{ province.col_province }}</strong>
                    </div>
                  </td>
                  <td class="px-8 py-2 whitespace-nowrap">{{ province.total_target }}</td> 
          <td class="px-8 py-2 whitespace-nowrap">{{ province.total_physical }}</td> 
          <td class="px-8 py-2 whitespace-nowrap">{{ calculatePercentageVariance(province.total_target, province.total_physical) }}</td> 
          <td class="px-8 py-2 whitespace-nowrap">{{ province.total_allocation }}</td> 
          <td class="px-8 py-2 whitespace-nowrap">{{ province.total_utilization }}</td> 
          <td class="px-8 py-2 whitespace-nowrap">{{ calculatePercentageVariance(province.total_allocation, province.total_utilization) }}</td> 
                </tr>
                <!-- City/Municipality data below each province -->
                <tr v-if="expandedProvince === province.psgc">
                  <td colspan="7" class="px-8 py-4"> 
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                      <div v-for="(district, districtKey) in province.districts" :key="districtKey">
                        <div v-if="districtKey && parseInt(districtKey)" class="font-bold mb-2">
                          {{ getOrdinal(parseInt(districtKey)) }} Congressional District
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 mt-2">
                          <thead class="bg-gray-100">
                            <tr>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City/Municipality</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Physical</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variance (Physical)</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocation</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variance (Financial)</th>
                            </tr>
                          </thead>
                          <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="city in district.cities" :key="city.col_citymuni">
                              <td class="px-8 py-2 whitespace-nowrap">{{ city.col_citymuni }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ city.total_target }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ city.total_physical }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ calculatePercentageVariance(city.total_target, city.total_physical) }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ city.total_allocation }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ city.total_utilization }}</td>
                      <td class="px-8 py-2 whitespace-nowrap">{{ calculatePercentageVariance(city.total_allocation, city.total_utilization) }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Programs Table -->
      <div v-else-if="activeView === 'programs'">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Program Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Served</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Variance (Physical)</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Allocation</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Utilization</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Variance (Financial)</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="program in filteredPrograms" :key="program.id">
                <td class="px-8 py-2 whitespace-nowrap flex items-center">
                <img
                v-if="program.program_logo"
                :src="program.program_logo"
                alt="Program Logo"
                class="w-8 h-8 mr-2 rounded-full"
                />
                <strong>{{ program.program_name }}</strong>
                </td>

                <td class="px-8 py-2 whitespace-nowrap">{{ program.total_target }}</td> <!-- Increased padding -->
          <td class="px-8 py-2 whitespace-nowrap">{{ program.total_physical }}</td> <!-- Increased padding -->
          <td class="px-8 py-2 whitespace-nowrap">{{calculatePercentageVariance(program.total_target, program.total_physical) }}</td>
          <td class="px-8 py-2 whitespace-nowrap">{{ program.total_allocation }}</td> <!-- Increased padding -->
          <td class="px-8 py-2 whitespace-nowrap">{{ program.total_utilization }}</td> <!-- Increased padding -->
          <td class="px-8 py-2 whitespace-nowrap">{{calculatePercentageVariance(program.total_allocation, program.total_utilization) }}</td> 
              </tr>
            </tbody>
          </table>
        </div>
      </div>
        
    </div>
  </div>
</template>

