<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const props = defineProps({
  allocations: {
    type: Array,
    default: () => []
  },
  utilizations: {
    type: Array,
    default: () => []
  }
});

const chartRef = ref(null);
let chartInstance = null;

const renderChart = () => {
  if (!Array.isArray(props.allocations) || !Array.isArray(props.utilizations)) {
    console.error('Allocations or Utilizations data is missing or not an array.');
    return;
  }

  // Sort the allocations and utilizations to put Davao City first
  const sortedAllocations = [...props.allocations].sort((a, b) => {
    if (a.province === 'Davao City') return -1; // Move Davao City to the front
    if (b.province === 'Davao City') return 1;
    return 0; // Keep the rest in original order
  });

  const sortedUtilizations = [...props.utilizations].sort((a, b) => {
    if (a.province === 'Davao City') return -1; // Move Davao City to the front
    if (b.province === 'Davao City') return 1;
    return 0; // Keep the rest in original order
  });

  // Destroy previous chart instance if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  // Create new chart instance
  chartInstance = new Chart(chartRef.value, {
    type: 'bar',
    data: {
      labels: sortedAllocations.map(a => a.province),
      datasets: [
        {
          label: 'Allocation',
          backgroundColor: 'blue',
          data: sortedAllocations.map(a => a.amount)
        },
        {
          label: 'Utilization',
          backgroundColor: 'red',
          data: sortedUtilizations.map(u => u.amount)
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, // Allow height to be defined by the container
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            display: false // Disable the grid lines on the y-axis
          }
        },
        x: {
          grid: {
            display: false // Disable the grid lines on the x-axis (if desired)
          }
        }
      }
    }
  });
};

// Watch for changes in props and re-render the chart
watch(() => props.allocations, renderChart);
watch(() => props.utilizations, renderChart);

onMounted(() => {
  renderChart();
});
</script>

<template>
  <div class="relative h-[41vh] w-full"> 
    <canvas class="w-full h-full" ref="chartRef"></canvas> 
  </div>
</template>
