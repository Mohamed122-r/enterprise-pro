<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Sales Overview</h3>
        <p class="text-sm text-gray-500">Monthly revenue performance</p>
      </div>
      
      <div class="flex items-center space-x-4">
        <select
          v-model="selectedPeriod"
          class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="loadChartData"
        >
          <option value="30">Last 30 Days</option>
          <option value="90">Last 90 Days</option>
          <option value="365">Last Year</option>
        </select>
      </div>
    </div>
    
    <div v-if="loading" class="flex justify-center items-center h-64">
      <LoadingSpinner size="medium" />
    </div>
    
    <div v-else class="h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
    
    <div class="mt-6 grid grid-cols-2 gap-4">
      <div class="text-center">
        <p class="text-sm text-gray-500">Total Revenue</p>
        <p class="text-2xl font-bold text-gray-900">
          {{ formatCurrency(totalRevenue) }}
        </p>
      </div>
      <div class="text-center">
        <p class="text-sm text-gray-500">Average Daily</p>
        <p class="text-2xl font-bold text-gray-900">
          {{ formatCurrency(averageRevenue) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import Chart from 'chart.js/auto'
import { reportApi } from '@/utils/api'
import { formatCurrency } from '@/utils/formatters'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'

const chartCanvas = ref(null)
const chartInstance = ref(null)
const loading = ref(false)
const selectedPeriod = ref('30')
const chartData = ref([])
const totalRevenue = ref(0)
const averageRevenue = ref(0)

const loadChartData = async () => {
  loading.value = true
  
  try {
    // In a real app, this would come from API
    // const response = await reportApi.salesChart(selectedPeriod.value)
    // chartData.value = response.data
    
    // Mock data for demonstration
    chartData.value = generateMockData(parseInt(selectedPeriod.value))
    calculateTotals()
    renderChart()
  } catch (error) {
    console.error('Failed to load chart data:', error)
  } finally {
    loading.value = false
  }
}

const generateMockData = (days) => {
  const data = []
  const today = new Date()
  
  for (let i = days - 1; i >= 0; i--) {
    const date = new Date(today)
    date.setDate(date.getDate() - i)
    
    data.push({
      date: date.toISOString().split('T')[0],
      revenue: Math.floor(Math.random() * 10000) + 5000,
      invoices: Math.floor(Math.random() * 20) + 5
    })
  }
  
  return data
}

const calculateTotals = () => {
  totalRevenue.value = chartData.value.reduce((sum, day) => sum + day.revenue, 0)
  averageRevenue.value = totalRevenue.value / chartData.value.length
}

const renderChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  if (!chartCanvas.value) return
  
  const ctx = chartCanvas.value.getContext('2d')
  
  chartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.value.map(d => {
        const date = new Date(d.date)
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
      }),
      datasets: [
        {
          label: 'Revenue',
          data: chartData.value.map(d => d.revenue),
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Invoices',
          data: chartData.value.map(d => d.invoices),
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          title: {
            display: true,
            text: 'Revenue ($)'
          },
          grid: {
            borderDash: [2, 2]
          }
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          title: {
            display: true,
            text: 'Invoices'
          },
          grid: {
            drawOnChartArea: false
          }
        }
      },
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              let label = context.dataset.label || ''
              if (label) {
                label += ': '
              }
              if (context.dataset.label === 'Revenue') {
                label += formatCurrency(context.parsed.y)
              } else {
                label += context.parsed.y
              }
              return label
            }
          }
        }
      }
    }
  })
}

onMounted(() => {
  loadChartData()
})

onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
})

watch(selectedPeriod, () => {
  loadChartData()
})
</script>