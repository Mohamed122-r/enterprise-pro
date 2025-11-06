<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Revenue Analytics</h3>
        <p class="text-sm text-gray-500">Income vs Expenses</p>
      </div>
      
      <div class="flex items-center space-x-4">
        <select
          v-model="chartType"
          class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="renderChart"
        >
          <option value="bar">Bar Chart</option>
          <option value="line">Line Chart</option>
        </select>
        
        <select
          v-model="selectedYear"
          class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="loadChartData"
        >
          <option
            v-for="year in availableYears"
            :key="year"
            :value="year"
          >
            {{ year }}
          </option>
        </select>
      </div>
    </div>
    
    <div v-if="loading" class="flex justify-center items-center h-64">
      <LoadingSpinner size="medium" />
    </div>
    
    <div v-else class="h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
    
    <div class="mt-6 grid grid-cols-3 gap-4 text-center">
      <div>
        <p class="text-sm text-gray-500">Total Revenue</p>
        <p class="text-xl font-bold text-green-600">
          {{ formatCurrency(totals.revenue) }}
        </p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Total Expenses</p>
        <p class="text-xl font-bold text-red-600">
          {{ formatCurrency(totals.expenses) }}
        </p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Net Profit</p>
        <p
          :class="[
            'text-xl font-bold',
            totals.net >= 0 ? 'text-green-600' : 'text-red-600'
          ]"
        >
          {{ formatCurrency(totals.net) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import Chart from 'chart.js/auto'
import { formatCurrency } from '@/utils/formatters'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'

const chartCanvas = ref(null)
const chartInstance = ref(null)
const loading = ref(false)
const chartType = ref('bar')
const selectedYear = ref(new Date().getFullYear())
const revenueData = ref([])

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  return Array.from({ length: 5 }, (_, i) => currentYear - i)
})

const totals = computed(() => {
  const revenue = revenueData.value.reduce((sum, month) => sum + month.revenue, 0)
  const expenses = revenueData.value.reduce((sum, month) => sum + month.expenses, 0)
  const net = revenue - expenses
  
  return { revenue, expenses, net }
})

const loadChartData = async () => {
  loading.value = true
  
  try {
    // Mock data - in real app, this would come from API
    revenueData.value = generateMockRevenueData(selectedYear.value)
    renderChart()
  } catch (error) {
    console.error('Failed to load revenue data:', error)
  } finally {
    loading.value = false
  }
}

const generateMockRevenueData = (year) => {
  const months = []
  
  for (let month = 0; month < 12; month++) {
    const revenue = Math.floor(Math.random() * 50000) + 30000
    const expenses = Math.floor(Math.random() * 30000) + 15000
    
    months.push({
      month: new Date(year, month).toLocaleDateString('en-US', { month: 'short' }),
      revenue,
      expenses,
      profit: revenue - expenses
    })
  }
  
  return months
}

const renderChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  if (!chartCanvas.value) return
  
  const ctx = chartCanvas.value.getContext('2d')
  
  chartInstance.value = new Chart(ctx, {
    type: chartType.value,
    data: {
      labels: revenueData.value.map(d => d.month),
      datasets: [
        {
          label: 'Revenue',
          data: revenueData.value.map(d => d.revenue),
          backgroundColor: chartType.value === 'bar' ? '#10b981' : 'transparent',
          borderColor: '#10b981',
          borderWidth: 2,
          fill: chartType.value === 'line'
        },
        {
          label: 'Expenses',
          data: revenueData.value.map(d => d.expenses),
          backgroundColor: chartType.value === 'bar' ? '#ef4444' : 'transparent',
          borderColor: '#ef4444',
          borderWidth: 2,
          fill: chartType.value === 'line'
        },
        {
          label: 'Profit',
          data: revenueData.value.map(d => d.profit),
          backgroundColor: chartType.value === 'bar' ? '#3b82f6' : 'transparent',
          borderColor: '#3b82f6',
          borderWidth: 2,
          fill: chartType.value === 'line'
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
          beginAtZero: true,
          title: {
            display: true,
            text: 'Amount ($)'
          },
          grid: {
            borderDash: [2, 2]
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
              label += formatCurrency(context.parsed.y)
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
</script>