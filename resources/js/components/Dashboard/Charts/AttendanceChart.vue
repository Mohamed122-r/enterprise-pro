<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Attendance Overview</h3>
        <p class="text-sm text-gray-500">Team presence and absence trends</p>
      </div>
      
      <div class="flex items-center space-x-4">
        <select
          v-model="selectedMonth"
          class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="loadChartData"
        >
          <option
            v-for="month in months"
            :key="month.value"
            :value="month.value"
          >
            {{ month.label }}
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
    
    <div class="mt-6 grid grid-cols-4 gap-4 text-center">
      <div>
        <p class="text-sm text-gray-500">Present</p>
        <p class="text-xl font-bold text-green-600">{{ stats.present }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Absent</p>
        <p class="text-xl font-bold text-red-600">{{ stats.absent }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Late</p>
        <p class="text-xl font-bold text-yellow-600">{{ stats.late }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Attendance Rate</p>
        <p class="text-xl font-bold text-blue-600">{{ stats.attendanceRate }}%</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import Chart from 'chart.js/auto'
import { reportApi } from '@/utils/api'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'

const chartCanvas = ref(null)
const chartInstance = ref(null)
const loading = ref(false)
const selectedMonth = ref(new Date().getMonth() + 1)
const attendanceData = ref([])

const months = computed(() => {
  const currentYear = new Date().getFullYear()
  return Array.from({ length: 12 }, (_, i) => {
    const date = new Date(currentYear, i, 1)
    return {
      value: i + 1,
      label: date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    }
  }).reverse()
})

const stats = computed(() => {
  const present = attendanceData.value.filter(a => a.status === 'present').length
  const absent = attendanceData.value.filter(a => a.status === 'absent').length
  const late = attendanceData.value.filter(a => a.status === 'late').length
  const total = attendanceData.value.length
  const attendanceRate = total > 0 ? Math.round((present / total) * 100) : 0
  
  return { present, absent, late, attendanceRate }
})

const loadChartData = async () => {
  loading.value = true
  
  try {
    // Mock data - in real app, this would come from API
    attendanceData.value = generateMockAttendanceData(selectedMonth.value)
    renderChart()
  } catch (error) {
    console.error('Failed to load attendance data:', error)
  } finally {
    loading.value = false
  }
}

const generateMockAttendanceData = (month) => {
  const year = new Date().getFullYear()
  const daysInMonth = new Date(year, month, 0).getDate()
  const data = []
  
  for (let day = 1; day <= daysInMonth; day++) {
    const statuses = ['present', 'present', 'present', 'present', 'present', 'late', 'absent']
    const randomStatus = statuses[Math.floor(Math.random() * statuses.length)]
    
    data.push({
      date: `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`,
      status: randomStatus,
      count: Math.floor(Math.random() * 50) + 30
    })
  }
  
  return data
}

const renderChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  if (!chartCanvas.value) return
  
  const ctx = chartCanvas.value.getContext('2d')
  
  // Group data by status for the selected month
  const statusCounts = attendanceData.value.reduce((acc, record) => {
    acc[record.status] = (acc[record.status] || 0) + 1
    return acc
  }, {})
  
  chartInstance.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Present', 'Absent', 'Late', 'Half Day'],
      datasets: [
        {
          data: [
            statusCounts.present || 0,
            statusCounts.absent || 0,
            statusCounts.late || 0,
            statusCounts.half_day || 0
          ],
          backgroundColor: [
            '#10b981',
            '#ef4444',
            '#f59e0b',
            '#3b82f6'
          ],
          borderWidth: 2,
          borderColor: '#ffffff'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.label || ''
              const value = context.parsed
              const total = context.dataset.data.reduce((a, b) => a + b, 0)
              const percentage = Math.round((value / total) * 100)
              return `${label}: ${value} (${percentage}%)`
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