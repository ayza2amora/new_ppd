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

  // Destroy previous chart instance if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  // Create new chart instance
  chartInstance = new Chart(chartRef.value, {
    type: 'bar',
    data: {
      labels: props.allocations.map(a => a.province),
      datasets: [
        {
          label: 'Allocation',
          backgroundColor: 'blue',
          data: props.allocations.map(a => a.amount)
        },
        {
          label: 'Utilization',
          backgroundColor: 'red',
          data: props.utilizations.map(u => u.amount)
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
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
  <div>
    <canvas class="max-w-6xl mx-auto relative h-[60vh] w-full" ref="chartRef"></canvas>
  </div>
</template>
